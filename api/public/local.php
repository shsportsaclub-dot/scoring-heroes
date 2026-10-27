<?php
declare(strict_types=1);
require_once __DIR__.'/../src/MatchEventJournal.php';
require_once __DIR__.'/../src/ScorerAccess.php';
require_once __DIR__.'/../src/LiveMatchView.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function respond(int $code,array $body):never{http_response_code($code);echo json_encode($body,JSON_THROW_ON_ERROR);exit;}
$path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
$method=$_SERVER['REQUEST_METHOD']??'GET';
if(!in_array($path,['/api/v1/local-events','/api/v1/local-view'],true))respond(404,['error'=>'Not found']);
if(($path==='/api/v1/local-events'&&$method!=='POST')||($path==='/api/v1/local-view'&&$method!=='GET'))respond(405,['error'=>'Method not allowed']);
$secret=getenv('SCORER_SIGNING_SECRET')?:'';
$dir=getenv('MATCH_EVENT_DIRECTORY')?:'';
$matchId=$_SERVER['HTTP_X_MATCH_ID']??'';
if(strlen($secret)<32||$dir===''||$dir[0]!=='/')respond(503,['error'=>'Service not configured']);
if(!is_string($matchId)||!preg_match('/^[a-zA-Z0-9_-]{1,80}$/',$matchId))respond(400,['error'=>'X-Match-Id required']);
$token='';
if(preg_match('/^Bearer (.+)$/',$_SERVER['HTTP_AUTHORIZATION']??'',$m))$token=$m[1];
try{$actor=ScorerAccess::verify($secret,$token,$matchId);}
catch(Throwable $e){respond(401,['error'=>'Unauthorized']);}
try{
    $journal=new MatchEventJournal($dir);
    if($method==='GET'){
        $events=$journal->read($matchId);
        respond(200,LiveMatchView::fromEvents($events,['id'=>$matchId,'title'=>'Local scored match']));
    }
    $length=(int)($_SERVER['CONTENT_LENGTH']??0);
    if($length<1||$length>8192)respond(413,['error'=>'Event size out of range']);
    $raw=file_get_contents('php://input',false,null,0,8193);
    if($raw===false||strlen($raw)>8192)respond(413,['error'=>'Event too large']);
    $event=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
    if(!is_array($event))respond(400,['error'=>'Invalid event']);
    foreach(['event_id','revision','sequence','match_id','innings','batter_id','bowler_id','bat_runs','extra_runs','extra_type','legal_delivery'] as $field)if(!array_key_exists($field,$event))respond(400,['error'=>'Missing '.$field]);
    if(($event['type']??null)!=='delivery'||!is_string($event['event_id'])||!preg_match('/^[A-Za-z0-9_-]{1,80}$/',$event['event_id'])||!is_int($event['revision'])||$event['revision']<1||!is_int($event['sequence'])||$event['sequence']<0)respond(400,['error'=>'Invalid identity']);
    try{ScorerAccess::authorizeEvent($actor,$event);}catch(RuntimeException $e){respond(403,['error'=>'Not authorized for this match or revision']);}
    ScorecardProjector::project([$event]);
    try{$result=$journal->append($matchId,$event,$actor['u']);}catch(DomainException $e){respond(409,['error'=>'Event revision or sequence conflict']);}
    respond($result['duplicate']?200:202,$result+['event_id'=>$event['event_id'],'revision'=>$event['revision']]);
}catch(JsonException|InvalidArgumentException $e){respond(400,['error'=>'Invalid event payload']);}
catch(Throwable $e){respond(503,['error'=>'Scoring service unavailable']);}
