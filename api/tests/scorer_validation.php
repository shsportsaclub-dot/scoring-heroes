<?php
declare(strict_types=1);
require_once __DIR__.'/../src/ScorerEventValidator.php';
$event=['event_id'=>'ball-1','revision'=>1,'sequence'=>1,'type'=>'delivery','innings'=>1,'batter_id'=>'bat-1','bowler_id'=>'bowl-1','bat_runs'=>4,'extra_runs'=>0,'extra_type'=>'none','legal_delivery'=>true];
ScorerEventValidator::validate($event,[]);
ScorerEventValidator::validate(array_replace($event,['revision'=>2,'bat_runs'=>6]),[$event]);
$cases=[array_replace($event,['revision'=>3]),array_replace($event,['event_id'=>'ball-2','sequence'=>3]),array_replace($event,['event_id'=>'ball-2','sequence'=>2,'bat_runs'=>-1])];
foreach($cases as $bad){
 try{ScorerEventValidator::validate($bad,[$event]);throw new RuntimeException('Invalid event accepted');}
 catch(InvalidArgumentException $expected){}
}
echo "PASS: event identity, sequential corrections, invalid event rejection\n";
