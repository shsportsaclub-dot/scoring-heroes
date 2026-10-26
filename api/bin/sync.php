<?php
declare(strict_types=1);
require_once __DIR__.'/../src/MatchStore.php';
require_once __DIR__.'/../src/CricketDataAdapter.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$key=getenv('CRICKETDATA_API_KEY')?:'';
$path=getenv('MATCH_CACHE_PATH')?:dirname(__DIR__,2).'/var/matches.json';
try{
    $adapter=new CricketDataAdapter($key);
    $matches=$adapter->currentMatches();
    (new MatchStore($path))->write($matches);
    fwrite(STDOUT,'Synced '.count($matches).' matches'.PHP_EOL);
}catch(Throwable $e){fwrite(STDERR,'Sync failed: '.$e->getMessage().PHP_EOL);exit(1);}
