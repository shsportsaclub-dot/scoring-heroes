<?php
declare(strict_types=1);
require_once __DIR__.'/../src/LiveMatchView.php';
function delivery(string $id,int $sequence,int $runs,bool $legal=true,string $extraType='none',int $extra=0):array {
    return ['type'=>'delivery','event_id'=>$id,'revision'=>1,'sequence'=>$sequence,'innings'=>2,'bat_runs'=>$runs,'extra_runs'=>$extra,'extra_type'=>$extraType,'batter_id'=>'b1','bowler_id'=>'p1','legal_delivery'=>$legal];
}
function check(bool $condition,string $message):void {if(!$condition)throw new RuntimeException($message);}
$events=[delivery('e1',1,4),delivery('e2',2,0,false,'wide',1),delivery('e3',3,6)];
$v=LiveMatchView::fromEvents($events,['innings'=>2,'target'=>21,'totalOvers'=>2]);
check($v['teams'][0]['score']==='11/0','Runs incorrect');
check($v['legalBalls']===2&&$v['ballsRemaining']===10,'Wide counted as legal delivery');
check($v['runsNeeded']===10&&$v['rrr']===6.0,'Required run rate incorrect');
check($v['chaseMessage']==='10 runs needed from 10 balls','Chase message incorrect');
$missing=LiveMatchView::fromEvents($events,['innings'=>2]);
check($missing['target']===null&&$missing['rrr']===null&&$missing['chaseMessage']===null,'Invented target or RRR');
$won=LiveMatchView::fromEvents($events,['innings'=>2,'target'=>11,'totalOvers'=>2]);
check($won['runsNeeded']===0&&$won['rrr']===null&&$won['chaseMessage']==='Target reached','Won chase incorrect');
echo "PASS: live chase metrics, wide legal-ball handling, missing metadata, target reached\n";
