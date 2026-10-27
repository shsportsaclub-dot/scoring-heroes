<?php
declare(strict_types=1);
require_once __DIR__.'/../src/ScorerService.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if($argc!==3){fwrite(STDERR,"Usage: php api/bin/score-event.php MATCH_ID EVENT_JSON_FILE\n");exit(2);}
$path=getenv('MATCH_EVENTS_PATH')?:dirname(__DIR__,2).'/var/events';
try{
    $raw=file_get_contents($argv[2]);if($raw===false)throw new RuntimeException('Input unavailable');
    $event=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
    if(!is_array($event))throw new InvalidArgumentException('JSON object required');
    $result=(new ScorerService($path))->record($argv[1],$event);
    echo json_encode($result,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES)."\n";
}catch(Throwable $e){fwrite(STDERR,'Rejected: '.$e->getMessage()."\n");exit(1);}
