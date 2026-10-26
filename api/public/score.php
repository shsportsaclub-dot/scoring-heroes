<?php
declare(strict_types=1);
require_once __DIR__.'/../src/ScorerService.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function respond(int $status,array $payload):never{http_response_code($status);echo json_encode($payload,JSON_THROW_ON_ERROR);exit;}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST')respond(405,['error'=>'POST required']);
$expected=getenv('SCORER_WRITE_TOKEN');
if(!is_string($expected)||strlen($expected)<32)respond(503,['error'=>'Scorer writes not configured']);
$auth=$_SERVER['HTTP_AUTHORIZATION']??'';
if(!preg_match('/^Bearer ([A-Za-z0-9_-]+)$/D',$auth,$m)||!hash_equals($expected,$m[1]))respond(401,['error'=>'Unauthorized']);
if(!str_starts_with($_SERVER['CONTENT_TYPE']??'','application/json'))respond(415,['error'=>'JSON required']);
$length=(int)($_SERVER['CONTENT_LENGTH']??0);
if($length<1||$length>8192)respond(413,['error'=>'Invalid payload size']);
$raw=file_get_contents('php://input',false,null,0,8193);
if(!is_string($raw)||strlen($raw)>8192)respond(413,['error'=>'Payload too large']);
try{
 $body=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
 if(!is_array($body)||!is_string($body['match_id']??null)||!is_array($body['event']??null))respond(400,['error'=>'match_id and event required']);
 $path=getenv('MATCH_EVENTS_PATH')?:dirname(__DIR__,2).'/var/events';
 $result=(new ScorerService($path))->record($body['match_id'],$body['event']);
 respond(200,['ok'=>true,'snapshot'=>$result]);
}catch(InvalidArgumentException $e){respond(422,['error'=>$e->getMessage()]);}
catch(Throwable $e){respond(503,['error'=>'Write unavailable']);}
