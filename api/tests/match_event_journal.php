<?php
declare(strict_types=1);
require_once __DIR__.'/../src/MatchEventJournal.php';
$dir=sys_get_temp_dir().'/sh-match-journal-'.bin2hex(random_bytes(5));
$j=new MatchEventJournal($dir);
$e=['type'=>'delivery','event_id'=>'b1','revision'=>1,'sequence'=>1,'match_id'=>'match1','innings'=>1,'bat_runs'=>4,'extra_runs'=>0,'batter_id'=>'a','bowler_id'=>'b','legal_delivery'=>true];
try{
    if($j->append('match1',$e,'scorer1')['duplicate'])throw new RuntimeException('First append duplicate');
    if(!$j->append('match1',$e,'scorer1')['duplicate'])throw new RuntimeException('Idempotency failed');
    $e['revision']=2;$e['bat_runs']=6;
    if($j->append('match1',$e,'admin1')['duplicate'])throw new RuntimeException('Correction duplicate');
    if(count($j->read('match1'))!==2||count($j->read('match2'))!==0)throw new RuntimeException('Match partition');
    $e['revision']=4;
    try{$j->append('match1',$e,'admin1');throw new RuntimeException('Gap accepted');}catch(DomainException $expected){}
    echo "PASS: per-match journal, idempotency, correction and gap rejection\n";
}finally{foreach(glob($dir.'/*')?:[] as $p)unlink($p);if(is_dir($dir))rmdir($dir);}
