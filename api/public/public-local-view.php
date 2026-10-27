<?php
declare(strict_types=1);
require_once __DIR__.'/../src/PublicSnapshot.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Methods: GET, HEAD');
if(!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD'],true)){http_response_code(405);header('Allow: GET, HEAD');echo '{"error":"Method not allowed"}';exit;}
$path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
if($path!=='/api/v1/public-local-view'){http_response_code(404);echo '{"error":"Not found"}';exit;}
if(getenv('LOCAL_MATCH_PUBLIC_ENABLED')!=='1'){http_response_code(404);echo '{"error":"Public match sharing disabled"}';exit;}
$snapshot=getenv('PUBLIC_SNAPSHOT_PATH')?:'';
if($snapshot===''||!str_starts_with($snapshot,'/')){http_response_code(503);echo '{"error":"Public feed not configured"}';exit;}
try{echo json_encode(PublicSnapshot::read($snapshot),JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);}
catch(Throwable $e){http_response_code(503);echo '{"error":"Public snapshot unavailable"}';}
