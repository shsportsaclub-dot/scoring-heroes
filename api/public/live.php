<?php
declare(strict_types=1);
require_once __DIR__.'/../src/ScorerService.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);echo json_encode(['error'=>'Method not allowed']);exit;}
$id=$_GET['match_id']??'';
if(!is_string($id)||!preg_match('/^[a-zA-Z0-9_-]{1,80}$/D',$id)){http_response_code(400);echo json_encode(['error'=>'Invalid match ID']);exit;}
$path=getenv('MATCH_EVENTS_PATH')?:dirname(__DIR__,2).'/var/events';
try {
    $result=(new ScorerService($path))->snapshot($id);
    echo json_encode($result,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
}catch(Throwable $e){http_response_code(503);echo json_encode(['error'=>'Snapshot unavailable']);}
