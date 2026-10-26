<?php
declare(strict_types=1);
require_once __DIR__.'/../src/EventJournal.php';
require_once __DIR__.'/../src/LiveMatchView.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function respond(int $code,array $body):never{http_response_code($code);echo json_encode($body,JSON_THROW_ON_ERROR);exit;}
$path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
$method=$_SERVER['REQUEST_METHOD']??'GET';
if(!in_array($path,['/api/v1/local-events','/api/v1/local-view'],true))respond(404,['error'=>'Not found']);
if($path==='/api/v1/local-view'&&$method!=='GET')respond(405,['error'=>'Method not allowed']);
if($path==='/api/v1/local-events'&&$method!=='POST')respond(405,['error'=>'Method not allowed']);
$secret=getenv('SCORER_INGEST_TOKEN')?:'';
$journalPath=getenv('EVENT_JOURNAL_PATH')?:'';
if(strlen($secret)<32||$journalPath===''||$journalPath[0]!=='/')respond(503,['error'=>'Service not configured']);
$provided='';
$header=$_SERVER['HTTP_AUTHORIZATION']??'';
if(preg_match('/^Bearer (.+)$/',$header,$m))$provided=$m[1];
if(!hash_equals($secret,$provided))respond(401,['error'=>'Unauthorized']);
$journal=new EventJournal($journalPath);
try{
    if($method==='GET'){
        $events=$journal->read();
        $view=LiveMatchView::fromEvents($events,['id'=>getenv('LOCAL_MATCH_ID')?:'local-match','title'=>getenv('LOCAL_MATCH_TITLE')?:'Local scored match']);
        respond(200,$view);
    }
    $length=(int)($_SERVER['CONTENT_LENGTH']??0);
    if($length<1||$length>8192)respond(413,['error'=>'Event size out of range']);
    $raw=file_get_contents('php://input',false,null,0,8193);
    if($raw===false||strlen($raw)>8192)respond(413,['error'=>'Event too large']);
    $event=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
    if(!is_array($event))respond(400,['error'=>'Invalid event']);
    foreach(['event_id','revision','sequence','innings','batter_id','bowler_id','bat_runs','extra_runs','extra_type','legal_delivery'] as $field)if(!array_key_exists($field,$event))respond(400,['error'=>'Missing '.$field]);
    if(($event['type']??null)!=='delivery'||!is_string($event['event_id'])||!preg_match('/^[A-Za-z0-9_-]{1,80}$/',$event['event_id'])||!is_int($event['revision'])||$event['revision']<1||!is_int($event['sequence'])||$event['sequence']<0)respond(400,['error'=>'Invalid identity']);
    // Validate the delivery against the same rules used by the projection before persisting.
    ScorecardProjector::project([$event]);
    $journal->append($event);
    respond(202,['accepted'=>true,'event_id'=>$event['event_id'],'revision'=>$event['revision']]);
}catch(JsonException|InvalidArgumentException $e){respond(400,['error'=>'Invalid event payload']);}
catch(Throwable $e){respond(503,['error'=>'Scoring service unavailable']);}
