<?php
declare(strict_types=1);
require_once __DIR__.'/../src/EventJournal.php';
require_once __DIR__.'/../src/LiveMatchView.php';
require_once __DIR__.'/../src/PublicSnapshot.php';
if(PHP_SAPI!=='cli')exit(2);
$journal=getenv('EVENT_JOURNAL_PATH')?:'';
$snapshot=getenv('PUBLIC_SNAPSHOT_PATH')?:'';
if($journal===''||$snapshot===''||$journal===$snapshot){fwrite(STDERR,"Configure distinct EVENT_JOURNAL_PATH and PUBLIC_SNAPSHOT_PATH\n");exit(2);}
try{
    $events=(new EventJournal($journal))->read();
    $view=LiveMatchView::fromEvents($events,['id'=>getenv('LOCAL_MATCH_ID')?:'local-match','title'=>getenv('LOCAL_MATCH_TITLE')?:'Local scored match']);
    PublicSnapshot::write($snapshot,$view);
    echo "Snapshot published (status: unverified)\n";
}catch(Throwable $e){fwrite(STDERR,"Snapshot publication failed\n");exit(1);}
