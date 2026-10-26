<?php
declare(strict_types=1);
require_once __DIR__.'/../src/MatchStore.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
$path=getenv('MATCH_CACHE_PATH')?:dirname(__DIR__,2).'/var/matches.json';
$uri=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
try {
    $data=(new MatchStore($path))->read();
    $age=$data['updated_at']?time()-strtotime($data['updated_at']):null;
    if($uri==='/api/v1/health'){echo json_encode(['ok'=>true,'cache_age_seconds'=>$age,'matches'=>count($data['matches'])],JSON_THROW_ON_ERROR);exit;}
    if($uri==='/api/v1/matches'){
        $status=$_GET['status']??null;
        $matches=$data['matches'];
        if($status!==null)$matches=array_values(array_filter($matches,fn($m)=>($m['status']??'')===$status));
        if($status==='live'&&($age===null||$age>180))$matches=[];
        echo json_encode(['updated_at'=>$data['updated_at'],'stale'=>$age===null||$age>180,'matches'=>$matches],JSON_THROW_ON_ERROR);exit;
    }
    if(preg_match('~^/api/v1/matches/([a-zA-Z0-9_-]+)$~',$uri,$hit)){
        foreach($data['matches'] as $m)if((string)$m['id']===$hit[1]){
            if(($m['status']??'')==='live'&&($age===null||$age>180)){$m['status']='stale';$m['summary']='Live data delayed — last confirmed: '.($data['updated_at']??'unknown');}
            echo json_encode($m,JSON_THROW_ON_ERROR);exit;
        }
    }
    http_response_code(404);echo json_encode(['error'=>'Not found']);
}catch(Throwable $e){http_response_code(503);echo json_encode(['error'=>'Match data unavailable']);}
