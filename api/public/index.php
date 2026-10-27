<?php
declare(strict_types=1);
require_once __DIR__.'/../src/MatchStore.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Methods: GET, HEAD');
if(!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD'],true)){
    header('Allow: GET, HEAD');http_response_code(405);echo json_encode(['error'=>'Method not allowed']);exit;
}
$path=getenv('MATCH_CACHE_PATH')?:dirname(__DIR__,2).'/var/matches.json';
$uri=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
function isStale(?int $age):bool {return $age===null||$age<0||$age>180;}
function sanitizedMatch(array $match,bool $stale):array {
    if($stale&&($match['status']??'')==='live'){
        $match['status']='stale';
        $match['summary']='Live data delayed; score not verified as current';
    }
    return $match;
}
try {
    $data=(new MatchStore($path))->read();
    $stamp=$data['updated_at']??null;
    $time=is_string($stamp)?strtotime($stamp):false;
    $age=$time!==false?time()-$time:null;
    $stale=isStale($age);
    $matches=array_map(fn($m)=>sanitizedMatch($m,$stale),$data['matches']);
    if($uri==='/api/v1/health'){
        echo json_encode(['ok'=>true,'cache_age_seconds'=>$age,'stale'=>$stale,'matches'=>count($matches)],JSON_THROW_ON_ERROR);exit;
    }
    if($uri==='/api/v1/matches'){
        $status=$_GET['status']??null;
        if($status!==null){
            if(!is_string($status)||!in_array($status,['live','upcoming','result','stale'],true)){http_response_code(400);echo json_encode(['error'=>'Invalid status']);exit;}
            $matches=array_values(array_filter($matches,fn($m)=>($m['status']??'')===$status));
        }
        echo json_encode(['updated_at'=>$stamp,'stale'=>$stale,'matches'=>$matches],JSON_THROW_ON_ERROR);exit;
    }
    if(preg_match('~^/api/v1/matches/([a-zA-Z0-9_-]+)$~',$uri,$hit)){
        foreach($matches as $m)if((string)$m['id']===$hit[1]){echo json_encode($m,JSON_THROW_ON_ERROR);exit;}
    }
    http_response_code(404);echo json_encode(['error'=>'Not found']);
}catch(Throwable $e){http_response_code(503);echo json_encode(['error'=>'Match data unavailable']);}
