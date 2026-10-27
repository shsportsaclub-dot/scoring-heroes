<?php
declare(strict_types=1);
require_once __DIR__.'/../src/ScorecardProjector.php';
function checkScore(bool $v,string $m):void{if(!$v)throw new RuntimeException($m);}
$base=['type'=>'delivery','innings'=>1,'batter_id'=>'b1','bowler_id'=>'p1','extra_type'=>'none','extra_runs'=>0,'legal_delivery'=>true];
$events=[
array_merge($base,['event_id'=>'1','revision'=>1,'sequence'=>1,'bat_runs'=>4]),
array_merge($base,['event_id'=>'2','revision'=>1,'sequence'=>2,'bat_runs'=>0,'extra_runs'=>1,'extra_type'=>'wide','legal_delivery'=>false]),
array_merge($base,['event_id'=>'3','revision'=>1,'sequence'=>3,'bat_runs'=>0,'extra_runs'=>2,'extra_type'=>'bye']),
array_merge($base,['event_id'=>'4','revision'=>1,'sequence'=>4,'bat_runs'=>0,'wicket'=>['kind'=>'run_out']]),
array_merge($base,['event_id'=>'4','revision'=>2,'sequence'=>4,'bat_runs'=>0,'wicket'=>['kind'=>'caught']])
];
$s=ScorecardProjector::project($events);
checkScore($s['innings'][1]['runs']===7,'Team runs');
checkScore($s['innings'][1]['wickets']===1,'Corrected wicket');
checkScore($s['innings'][1]['overs']==='0.3','Legal deliveries');
checkScore($s['batters'][0]['runs']===4,'Batter runs');
checkScore($s['batters'][0]['balls']===3,'Wide not batter ball');
checkScore($s['bowlers'][0]['runs']===5,'Byes excluded from bowler');
checkScore($s['bowlers'][0]['wickets']===1,'Caught credits bowler');
echo "PASS: scorecard projection, extras, correction, wicket credit".PHP_EOL;
