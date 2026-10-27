<?php
declare(strict_types=1);
require_once __DIR__.'/../src/EventJournal.php';
require_once __DIR__.'/../src/LiveMatchView.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$journal=getenv('EVENT_JOURNAL_PATH');
if(!$journal){fwrite(STDERR,"Set EVENT_JOURNAL_PATH to a trusted local JSONL file\n");exit(2);}
try{
    $events=(new EventJournal($journal))->read();
    $view=LiveMatchView::fromEvents($events,['id'=>getenv('LOCAL_MATCH_ID')?:'local-match','title'=>getenv('LOCAL_MATCH_TITLE')?:'Local match']);
    echo json_encode($view,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
}catch(Throwable $e){fwrite(STDERR,"Unable to project local match\n");exit(1);}
