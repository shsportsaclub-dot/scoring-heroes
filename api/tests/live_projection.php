<?php
declare(strict_types=1);
require_once __DIR__.'/../src/LiveMatchProjector.php';
$base=['type'=>'delivery','innings'=>1,'batter_id'=>'b1','bowler_id'=>'p1','extra_type'=>'none','extra_runs'=>0,'legal_delivery'=>true];
$events=[];
for($i=1;$i<=6;$i++)$events[]=array_merge($base,['event_id'=>'ball-'.$i,'revision'=>1,'sequence'=>$i,'bat_runs'=>$i===1?4:0]);
$events[]=array_merge($base,['event_id'=>'ball-7','revision'=>1,'sequence'=>7,'bat_runs'=>1]);
$result=LiveMatchProjector::project($events,['b1'=>'Batter One']);
if($result['scorecard']['innings'][1]['runs']!==5)throw new RuntimeException('Total mismatch');
if($result['current_over']['number']!==2)throw new RuntimeException('Over transition mismatch');
if(count($result['current_over']['balls'])!==1)throw new RuntimeException('Over ball list mismatch');
if(count($result['commentary'])!==7)throw new RuntimeException('Commentary count mismatch');
echo "PASS: scorecard + over strip + basic commentary projection".PHP_EOL;
