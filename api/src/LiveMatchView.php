<?php
declare(strict_types=1);
require_once __DIR__.'/ScorecardProjector.php';
final class LiveMatchView {
    public static function fromEvents(array $events,array $meta=[]):array {
        $score=ScorecardProjector::project($events);
        $n=(int)($meta['innings']??(count($score['innings'])?max(array_keys($score['innings'])):1));
        $inn=$score['innings'][$n]??['runs'=>0,'wickets'=>0,'overs'=>'0.0','legal_balls'=>0];
        $batters=array_values(array_filter($score['batters'],static fn($b)=>$b['innings']===$n));
        $bowlers=array_values(array_filter($score['bowlers'],static fn($b)=>$b['innings']===$n));
        $ordered=[];
        foreach($events as $e)if(($e['type']??null)==='delivery'&&($e['innings']??null)===$n)$ordered[$e['event_id']]=!isset($ordered[$e['event_id']])||$e['revision']>$ordered[$e['event_id']]['revision']?$e:$ordered[$e['event_id']];
        $ordered=array_values($ordered);
        usort($ordered,static fn($a,$b)=>($a['sequence']<=>$b['sequence'])?:strcmp($a['event_id'],$b['event_id']));
        $last=array_slice($ordered,-12);
        $commentary=array_map(static function($e){$run=(int)$e['bat_runs']+(int)$e['extra_runs'];return ['id'=>$e['event_id'],'text'=>'Delivery '.$e['sequence'].': '.$run.' run(s)'.(isset($e['wicket'])?' · WICKET':'')];},array_reverse($last));
        $batViews=array_map(static fn($b)=>['name'=>$b['id'],'runs'=>$b['runs'],'balls'=>$b['balls'],'fours'=>$b['fours'],'sixes'=>$b['sixes'],'sr'=>$b['strike_rate']],$batters);
        $lastBowler=end($ordered);$bowlerId=$lastBowler['bowler_id']??null;
        $bowl=null;foreach($bowlers as $p)if($p['id']===$bowlerId){$bowl=['name'=>$p['id'],'wickets'=>$p['wickets'],'runs'=>$p['runs'],'overs'=>$p['overs'],'economy'=>$p['economy']];break;}
        $overs=(string)$inn['overs'];
        $crr=$inn['legal_balls']?round($inn['runs']*6/$inn['legal_balls'],2):null;
        return ['id'=>(string)($meta['id']??'local-demo'),'title'=>(string)($meta['title']??'Local scored match'),'status'=>'unverified','teams'=>[['name'=>(string)($meta['batting_team']??'Batting side'),'score'=>$inn['runs'].'/'.$inn['wickets']],['name'=>(string)($meta['bowling_team']??'Bowling side'),'score'=>'—']],'inningsLabel'=>'Innings '.$n,'overs'=>$overs,'crr'=>$crr,'session'=>null,'oversLeftToday'=>null,'summary'=>'Local scorer events · not verified as a live provider feed','breakLabel'=>'Local scoring','currentOver'=>['number'=>(string)(intdiv((int)$inn['legal_balls'],6)+1),'balls'=>array_map(static fn($e)=>(string)((int)$e['bat_runs']+(int)$e['extra_runs']),array_slice($last,-6)),'runs'=>array_sum(array_map(static fn($e)=>(int)$e['bat_runs']+(int)$e['extra_runs'],array_slice($last,-6)))],'batters'=>$batViews,'bowler'=>$bowl,'events'=>$commentary,'scorecard'=>[['name'=>(string)($meta['batting_team']??'Batting side'),'runs'=>$inn['runs'],'wickets'=>$inn['wickets']]],'updated_at'=>gmdate('c')];
    }
}
