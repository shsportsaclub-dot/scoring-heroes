<?php
declare(strict_types=1);
require_once __DIR__.'/../src/PublicSnapshot.php';
$dir=sys_get_temp_dir().'/sh-public-test-'.bin2hex(random_bytes(6));
$path=$dir.'/snapshot.json';
try{
    PublicSnapshot::write($path,['id'=>'m1','title'=>'Test','status'=>'live','teams'=>[['name'=>'A','score'=>'4/0'],['name'=>'B','score'=>'—']],'events'=>[],'private_token'=>'must-not-leak']);
    $raw=file_get_contents($path);
    if(str_contains($raw,'must-not-leak'))throw new RuntimeException('Sensitive field leaked');
    $data=PublicSnapshot::read($path);
    if($data['status']!=='unverified'||$data['stale']!==false||$data['teams'][0]['score']!=='4/0')throw new RuntimeException('Snapshot projection failed');
    echo "PASS: public snapshot sanitized and readable\n";
}finally{if(is_file($path))unlink($path);if(is_dir($dir))rmdir($dir);}
