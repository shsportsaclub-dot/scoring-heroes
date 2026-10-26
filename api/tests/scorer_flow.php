<?php
declare(strict_types=1);
require_once __DIR__.'/../src/ScorerService.php';
$dir=sys_get_temp_dir().'/sh-scorer-'.bin2hex(random_bytes(5));
try{
    $service=new ScorerService($dir);
    $base=['event_id'=>'m1-1','revision'=>1,'sequence'=>1,'type'=>'delivery','innings'=>1,'bat_runs'=>4,'extra_runs'=>0,'extra_type'=>'none','batter_id'=>'b1','bowler_id'=>'p1','legal_delivery'=>true];
    $first=$service->record('m1',$base);
    if($first['projection']['scorecard']['innings'][1]['runs']!==4)throw new RuntimeException('Score not saved');
    $fixed=$service->record('m1',array_replace($base,['revision'=>2,'bat_runs'=>6]));
    if($fixed['projection']['scorecard']['innings'][1]['runs']!==6)throw new RuntimeException('Correction failed');
    $count=$fixed['event_count'];
    try{$service->record('m1',array_replace($base,['event_id'=>'bad','sequence'=>2,'bat_runs'=>-1]));throw new RuntimeException('Invalid event accepted');}
    catch(InvalidArgumentException $expected){}
    if($service->snapshot('m1')['event_count']!==$count)throw new RuntimeException('Invalid event was written');
    echo "PASS: scorer event -> journal -> correction -> viewer snapshot\n";
}finally{
    foreach(glob($dir.'/*')?:[] as $f)unlink($f);
    if(is_dir($dir))rmdir($dir);
}
