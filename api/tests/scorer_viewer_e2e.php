<?php
declare(strict_types=1);
require_once __DIR__.'/../src/MatchEventJournal.php';
require_once __DIR__.'/../src/LiveMatchView.php';
require_once __DIR__.'/../src/PublicSnapshot.php';
$root=sys_get_temp_dir().'/sh-e2e-'.bin2hex(random_bytes(6));
$journal=$root.'/events';$snapshot=$root.'/public/match.json';
try{
    $store=new MatchEventJournal($journal);
    $base=['type'=>'delivery','match_id'=>'match1','innings'=>1,'batter_id'=>'a','bowler_id'=>'b','extra_runs'=>0,'extra_type'=>'none','legal_delivery'=>true];
    $store->append('match1',array_merge($base,['event_id'=>'e1','revision'=>1,'sequence'=>1,'bat_runs'=>4]),'scorer');
    $store->append('match1',array_merge($base,['event_id'=>'e2','revision'=>1,'sequence'=>2,'bat_runs'=>1]),'scorer');
    $view=LiveMatchView::fromEvents($store->read('match1'),['id'=>'match1','title'=>'Fixture']);
    PublicSnapshot::write($snapshot,$view);
    $published=PublicSnapshot::read($snapshot);
    if($published['teams'][0]['score']!=='5/0'||$published['overs']!=='0.2'||$published['status']!=='unverified')throw new RuntimeException('Initial snapshot mismatch');
    $store->append('match1',array_merge($base,['event_id'=>'e1','revision'=>2,'sequence'=>1,'bat_runs'=>6]),'admin');
    $view=LiveMatchView::fromEvents($store->read('match1'),['id'=>'match1','title'=>'Fixture']);
    PublicSnapshot::write($snapshot,$view);
    $corrected=PublicSnapshot::read($snapshot);
    if($corrected['teams'][0]['score']!=='7/0'||$corrected['currentOver']['balls']!==['6','1'])throw new RuntimeException('Corrected snapshot mismatch');
    echo "PASS: match journal → correction → live projection → public snapshot\n";
}finally{
    foreach([$snapshot,$journal.'/match1.jsonl'] as $file)if(is_file($file))unlink($file);
    foreach([$root.'/public',$journal,$root] as $dir)if(is_dir($dir))rmdir($dir);
}
