<?php
declare(strict_types=1);
require_once __DIR__.'/ScorecardProjector.php';
require_once __DIR__.'/OverTimeline.php';
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
        // Compute over labels from legal deliveries; wides/no-balls do not
        // advance the ball count. Revisions replace their original delivery.
        $annotated=[];$legalSeen=0;
        foreach($ordered as $e){
            $over=intdiv($legalSeen,6);
            $ball=($legalSeen%6)+1;
            $annotated[]=['event'=>$e,'over'=>$over.'.'.$ball];
            if(($e['legal_delivery']??false)===true)$legalSeen++;
        }
        $last=array_slice($annotated,-12);
        $commentary=array_map(static function($item){
            $e=$item['event'];
            $bat=(int)($e['bat_runs']??0);
            $extra=(int)($e['extra_runs']??0);
            $kind=(string)($e['extra_type']??'none');
            $wicket=isset($e['wicket']);
            $description=$wicket?'WICKET':($bat===6?'SIX':($bat===4?'FOUR':($bat+$extra).' run(s)'));
            if($kind!=='none')$description.=' · '.str_replace('_',' ',$kind).' +'.$extra;
            return ['id'=>$e['event_id'],'over'=>$item['over'],'text'=>$description.' · '.($e['batter_id']??'Batter').' vs '.($e['bowler_id']??'Bowler')];
        },array_reverse($last));
        $batViews=array_map(static fn($b)=>['name'=>$b['id'],'runs'=>$b['runs'],'balls'=>$b['balls'],'fours'=>$b['fours'],'sixes'=>$b['sixes'],'sr'=>$b['strike_rate']],$batters);
        $lastBowler=end($ordered);$bowlerId=$lastBowler['bowler_id']??null;
        $bowl=null;foreach($bowlers as $p)if($p['id']===$bowlerId){$bowl=['name'=>$p['id'],'wickets'=>$p['wickets'],'runs'=>$p['runs'],'overs'=>$p['overs'],'economy'=>$p['economy']];break;}
        $timeline=OverTimeline::project($events,$n);
        $overs=(string)$inn['overs'];
        $crr=$inn['legal_balls']?round($inn['runs']*6/$inn['legal_balls'],2):null;
        // Chase calculations use legal balls, not textual over decimals. Missing
        // target/overs must never be fabricated for the public viewer.
        $target=isset($meta['target'])&&is_int($meta['target'])&&$meta['target']>0?$meta['target']:null;
        $totalOvers=isset($meta['totalOvers'])&&is_int($meta['totalOvers'])&&$meta['totalOvers']>0?$meta['totalOvers']:null;
        $ballsRemaining=$totalOvers===null?null:max(0,$totalOvers*6-$inn['legal_balls']);
        $runsNeeded=$target===null?null:max(0,$target-$inn['runs']);
        $rrr=$target!==null&&$ballsRemaining!==null&&$ballsRemaining>0&&$runsNeeded>0?round($runsNeeded*6/$ballsRemaining,2):null;
        $chaseMessage=$target===null||$ballsRemaining===null?null:($runsNeeded===0?'Target reached':($ballsRemaining===0?'Target not reached in allotted overs':$runsNeeded.' runs needed from '.$ballsRemaining.' balls'));
        return ['id'=>(string)($meta['id']??'local-demo'),'title'=>(string)($meta['title']??'Local scored match'),'status'=>'unverified','teams'=>[['name'=>(string)($meta['batting_team']??'Batting side'),'score'=>$inn['runs'].'/'.$inn['wickets']],['name'=>(string)($meta['bowling_team']??'Bowling side'),'score'=>'—']],'inningsLabel'=>'Innings '.$n,'overs'=>$overs,'legalBalls'=>$inn['legal_balls'],'totalOvers'=>$totalOvers,'target'=>$target,'runsNeeded'=>$runsNeeded,'ballsRemaining'=>$ballsRemaining,'rrr'=>$rrr,'chaseMessage'=>$chaseMessage,'crr'=>$crr,'session'=>null,'oversLeftToday'=>null,'summary'=>'Local scorer events · not verified as a live provider feed','breakLabel'=>'Local scoring','currentOver'=>$timeline['current_over'],'overTimeline'=>$timeline['overs'],'batters'=>$batViews,'bowler'=>$bowl,'events'=>$commentary,'scorecard'=>[['name'=>(string)($meta['batting_team']??'Batting side'),'runs'=>$inn['runs'],'wickets'=>$inn['wickets']]],'updated_at'=>gmdate('c')];
    }
}
