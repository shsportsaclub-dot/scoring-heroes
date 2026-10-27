<?php
declare(strict_types=1);
require_once __DIR__.'/ScorecardProjector.php';
final class LiveMatchProjector {
    public static function project(array $events, array $roster=[]): array {
        $latest=[];
        foreach($events as $e){
            if(!is_array($e)||!is_string($e['event_id']??null)||!is_int($e['revision']??null)||!is_int($e['sequence']??null))throw new InvalidArgumentException('Invalid event identity');
            if(!isset($latest[$e['event_id']])||$e['revision']>$latest[$e['event_id']]['revision'])$latest[$e['event_id']]=$e;
        }
        $ordered=array_values($latest);
        usort($ordered,static fn($a,$b)=>($a['sequence']<=>$b['sequence'])?:strcmp($a['event_id'],$b['event_id']));
        $score=ScorecardProjector::project($ordered);
        $deliveries=array_values(array_filter($ordered,static fn($e)=>($e['type']??null)==='delivery'));
        $last=end($deliveries);
        if($last===false)return ['scorecard'=>$score,'current_over'=>null,'commentary'=>[],'last_delivery'=>null];
        $inn=(int)$last['innings'];
        $inEvents=array_values(array_filter($deliveries,static fn($e)=>(int)$e['innings']===$inn));
        $legal=0;$current=[];$overNumber=1;
        foreach($inEvents as $e){
            if($legal>0&&$legal%6===0&&($e['legal_delivery']??false)===true){
                $overNumber=intdiv($legal,6)+1;$current=[];
            }
            $bat=(int)($e['bat_runs']??0);$extras=(int)($e['extra_runs']??0);
            $kind=$e['extra_type']??'none';
            $token=(string)($bat+$extras);
            if($kind==='wide')$token.='wd';
            elseif($kind==='no_ball')$token.='nb';
            elseif($kind==='bye')$token.='b';
            elseif($kind==='leg_bye')$token.='lb';
            if(isset($e['wicket']))$token.=' W';
            $current[]=['event_id'=>$e['event_id'],'token'=>$token];
            if($e['legal_delivery'])$legal++;
        }
        $commentary=[];
        foreach(array_reverse($inEvents) as $e){
            $name=$roster[$e['batter_id']]??$e['batter_id'];
            $runs=(int)$e['bat_runs']+(int)$e['extra_runs'];
            $commentary[]=['event_id'=>$e['event_id'],'text'=>$name.': '.$runs.' run(s)'.(isset($e['wicket'])?' — WICKET':'')];
        }
        return ['scorecard'=>$score,'current_over'=>['number'=>$overNumber,'balls'=>$current],'commentary'=>$commentary,'last_delivery'=>$last['event_id']];
    }
}
