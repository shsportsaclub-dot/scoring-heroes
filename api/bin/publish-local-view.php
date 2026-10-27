<?php
declare(strict_types=1);
require_once __DIR__.'/../src/MatchEventJournal.php';
require_once __DIR__.'/../src/LiveMatchView.php';
require_once __DIR__.'/../src/PublicSnapshot.php';
if(PHP_SAPI!=='cli')exit(2);
$dir=getenv('MATCH_EVENT_DIRECTORY')?:'';
$snapshot=getenv('PUBLIC_SNAPSHOT_PATH')?:'';
$matchId=getenv('LOCAL_MATCH_ID')?:'';
if($dir===''||$dir[0]!=='/'||$snapshot===''||$snapshot[0]!=='/'||!preg_match('/^[A-Za-z0-9_-]{1,80}$/',$matchId)){fwrite(STDERR,"Configure MATCH_EVENT_DIRECTORY, PUBLIC_SNAPSHOT_PATH and LOCAL_MATCH_ID\n");exit(2);}
try{
    $events=(new MatchEventJournal($dir))->read($matchId);
    if(!$events){fwrite(STDERR,"No match events; refusing empty snapshot\n");exit(3);}
    $view=LiveMatchView::fromEvents($events,['id'=>$matchId,'title'=>getenv('LOCAL_MATCH_TITLE')?:'Local scored match']);
    PublicSnapshot::write($snapshot,$view);
    echo "Published match ".$matchId." (unverified)\n";
}catch(Throwable $e){fwrite(STDERR,"Snapshot publication failed\n");exit(1);}
