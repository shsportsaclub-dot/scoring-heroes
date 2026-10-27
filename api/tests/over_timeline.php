<?php
declare(strict_types=1);
require_once __DIR__.'/../src/OverTimeline.php';
function checkOver(bool $ok,string $message):void{if(!$ok)throw new RuntimeException($message);}
$base=['type'=>'delivery','revision'=>1,'innings'=>1,'batter_id'=>'a','bowler_id'=>'b','bat_runs'=>0,'extra_runs'=>0,'extra_type'=>'none','legal_delivery'=>true];
$events=[];
for($i=1;$i<=6;$i++)$events[]=array_merge($base,['event_id'=>'e'.$i,'sequence'=>$i,'bat_runs'=>1]);
$events[]=array_merge($base,['event_id'=>'wide','sequence'=>7,'extra_type'=>'wide','extra_runs'=>1,'legal_delivery'=>false]);
$events[]=array_merge($base,['event_id'=>'e7','sequence'=>8,'bat_runs'=>4]);
$events[]=array_merge($base,['event_id'=>'e7','revision'=>2,'sequence'=>8,'bat_runs'=>2]);
$timeline=OverTimeline::project($events,1);
checkOver($timeline['overs_display']==='1.1','Legal over count');
checkOver(count($timeline['overs'])===2,'Over grouping');
checkOver($timeline['overs'][0]['completed']===true,'Completed first over');
checkOver($timeline['current_over']['balls']===['1wd','2'],'Wide and corrected ball');
checkOver($timeline['current_over']['runs']===3,'Over total after correction');
echo "PASS: legal-ball over grouping, wides and revisions\n";
