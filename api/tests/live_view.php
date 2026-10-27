<?php
declare(strict_types=1);
require_once __DIR__.'/../src/LiveMatchView.php';
$e=['type'=>'delivery','event_id'=>'e1','revision'=>1,'sequence'=>1,'innings'=>1,'bat_runs'=>4,'extra_runs'=>0,'extra_type'=>'none','batter_id'=>'b1','bowler_id'=>'p1','legal_delivery'=>true];
$v=LiveMatchView::fromEvents([$e],['id'=>'test-1']);
if($v['teams'][0]['score']!=='4/0'||$v['overs']!=='0.1'||$v['batters'][0]['runs']!==4||$v['bowler']['runs']!==4||$v['status']!=='unverified')throw new RuntimeException('Projection failed');
echo "PASS: normalized delivery to LIVE UI payload\n";
