<?php
declare(strict_types=1);
require_once __DIR__.'/../src/MatchStore.php';
require_once __DIR__.'/../src/CricketDataAdapter.php';
function check(bool $condition,string $message):void {if(!$condition)throw new RuntimeException($message);}
$dir=sys_get_temp_dir().'/sh-match-test-'.bin2hex(random_bytes(5));
mkdir($dir,0700,true);
$path=$dir.'/matches.json';
try {
    $store=new MatchStore($path);
    check($store->read()['matches']===[],'Empty cache should contain no matches');
    $matches=[['id'=>'test-1','status'=>'upcoming','teams'=>[['name'=>'A','score'=>'—'],['name'=>'B','score'=>'—']]]];
    $store->write($matches);
    $saved=$store->read();
    check(count($saved['matches'])===1,'One match should be saved');
    check($saved['matches'][0]['id']==='test-1','Match ID must be preserved');
    check(!empty($saved['updated_at']),'Cache must have update timestamp');
    try {new CricketDataAdapter('','https://api.cricapi.com/v1');throw new RuntimeException('Missing key accepted');}
    catch(InvalidArgumentException $expected){}
    try {new CricketDataAdapter('placeholder','http://invalid');throw new RuntimeException('Insecure URL accepted');}
    catch(InvalidArgumentException $expected){}
    echo "PASS: cache roundtrip, timestamps, provider configuration guards".PHP_EOL;
} finally {if(is_file($path))unlink($path);rmdir($dir);}
