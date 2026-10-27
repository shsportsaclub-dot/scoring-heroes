<?php
declare(strict_types=1);
require_once __DIR__.'/../src/MatchStore.php';
require_once __DIR__.'/../src/CricketDataAdapter.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$key=getenv('CRICKETDATA_API_KEY')?:'';
$path=getenv('MATCH_CACHE_PATH')?:dirname(__DIR__,2).'/var/matches.json';
try{
    $adapter=new CricketDataAdapter($key);
    // CurrentMatches is paginated. Fetch bounded pages and deduplicate by ID.
    // Stop on an empty page or when no new IDs appear; avoid unbounded API spend.
    $pages=max(1,min(4,(int)(getenv('CRICKETDATA_SYNC_PAGES')?:2)));
    $byId=[];
    for($page=0;$page<$pages;$page++){
        $batch=$adapter->currentMatches($page*25);
        if(!$batch)break;
        $before=count($byId);
        foreach($batch as $match)$byId[$match['id']]=$match;
        if(count($byId)===$before)break;
    }
    $matches=array_values($byId);
    (new MatchStore($path))->write($matches);
    fwrite(STDOUT,'Synced '.count($matches).' matches'.PHP_EOL);
}catch(Throwable $e){fwrite(STDERR,'Sync failed: '.$e->getMessage().PHP_EOL);exit(1);}
