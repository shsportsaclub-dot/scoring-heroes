<?php
declare(strict_types=1);
require_once __DIR__.'/../src/EventJournal.php';
require_once __DIR__.'/../src/LiveMatchView.php';
require_once __DIR__.'/../src/PublicSnapshot.php';
$dir=sys_get_temp_dir().'/scoring-heroes-e2e-'.bin2hex(random_bytes(6));
if(!mkdir($dir,0700))throw new RuntimeException('Cannot make test directory');
$journalPath=$dir.'/events.jsonl';
$snapshotPath=$dir.'/public.json';
try {
    $journal=new EventJournal($journalPath);
    $base=['type'=>'delivery','innings'=>1,'batter_id'=>'batter-1','bowler_id'=>'bowler-1','extra_runs'=>0,'extra_type'=>'none','legal_delivery'=>true];
    $journal->append(array_merge($base,['event_id'=>'ball-1','revision'=>1,'sequence'=>1,'bat_runs'=>1]));
    $journal->append(array_merge($base,['event_id'=>'ball-2','revision'=>1,'sequence'=>2,'bat_runs'=>4]));
    $journal->append(array_merge($base,['event_id'=>'ball-2','revision'=>2,'sequence'=>2,'bat_runs'=>6]));
    $view=LiveMatchView::fromEvents($journal->read(),['id'=>'test-match','title'=>'Integration fixture']);
    if($view['teams'][0]['score']!=='7/0')throw new RuntimeException('Corrected score not reflected');
    if($view['currentOver']['balls']!==['1','6'])throw new RuntimeException('Corrected balls not reflected');
    PublicSnapshot::write($snapshotPath,$view);
    $public=PublicSnapshot::read($snapshotPath);
    if($public['teams'][0]['score']!=='7/0'||$public['status']!=='unverified'||$public['stale']!==false)throw new RuntimeException('Public viewer snapshot mismatch');
    if(file_get_contents($journalPath)===file_get_contents($snapshotPath))throw new RuntimeException('Private journal exposed as snapshot');
    echo "PASS: journal -> corrected scorecard -> live view -> public snapshot\n";
} finally {
    foreach([$journalPath,$snapshotPath] as $path)if(is_file($path))unlink($path);
    rmdir($dir);
}
