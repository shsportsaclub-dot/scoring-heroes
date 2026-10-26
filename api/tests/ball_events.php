<?php
declare(strict_types=1);
require_once __DIR__.'/../src/BallEventReducer.php';
function expectResult(bool $ok,string $message):void{if(!$ok)throw new RuntimeException($message);}
$base=['type'=>'delivery','innings'=>1,'wickets'=>0,'legal_delivery'=>true];
$events=[
    array_merge($base,['event_id'=>'ball-1','sequence'=>1,'revision'=>1,'runs'=>4]),
    array_merge($base,['event_id'=>'ball-1','sequence'=>1,'revision'=>1,'runs'=>4]),
    array_merge($base,['event_id'=>'ball-2','sequence'=>2,'revision'=>1,'runs'=>1,'legal_delivery'=>false]),
    array_merge($base,['event_id'=>'ball-3','sequence'=>3,'revision'=>1,'runs'=>0,'wickets'=>1])
];
$state=BallEventReducer::reduce($events);
expectResult($state['innings'][1]['runs']===5,'Duplicate double counted');
expectResult($state['innings'][1]['wickets']===1,'Wicket missing');
expectResult($state['innings'][1]['overs']==='0.2','Illegal ball counted');
$events[]=array_merge($base,['event_id'=>'ball-1','sequence'=>1,'revision'=>2,'runs'=>6]);
expectResult(BallEventReducer::reduce($events)['innings'][1]['runs']===7,'Correction failed');
$events[]=array_merge($base,['event_id'=>'ball-1','sequence'=>1,'revision'=>1,'runs'=>1]);
expectResult(BallEventReducer::reduce($events)['innings'][1]['runs']===7,'Old revision applied');
try{BallEventReducer::reduce([array_merge($base,['event_id'=>'bad','sequence'=>1,'revision'=>1,'runs'=>-1])]);throw new RuntimeException('Negative runs accepted');}
catch(InvalidArgumentException $expected){}
echo "PASS: duplicates, revisions, illegal deliveries, wickets, overs".PHP_EOL;
