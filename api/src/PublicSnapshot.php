<?php
declare(strict_types=1);
final class PublicSnapshot {
    public static function write(string $path,array $view):void {
        if(!str_starts_with($path,'/'))throw new InvalidArgumentException('Absolute path required');
        $dir=dirname($path);
        if(!is_dir($dir)&&!mkdir($dir,0700,true)&&!is_dir($dir))throw new RuntimeException('Snapshot directory unavailable');
        $allowed=['id','title','status','teams','inningsLabel','overs','crr','session','oversLeftToday','summary','breakLabel','currentOver','batters','bowler','events','scorecard','updated_at'];
        $clean=array_intersect_key($view,array_flip($allowed));
        $clean['status']='unverified';
        $clean['published_at']=gmdate('c');
        $json=json_encode($clean,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
        if(strlen($json)>262144)throw new RuntimeException('Snapshot exceeds limit');
        $tmp=tempnam($dir,'.snapshot-');
        if($tmp===false)throw new RuntimeException('Cannot create snapshot');
        try{
            if(file_put_contents($tmp,$json,LOCK_EX)!==strlen($json))throw new RuntimeException('Snapshot write failed');
            chmod($tmp,0600);
            if(!rename($tmp,$path))throw new RuntimeException('Snapshot swap failed');
        }finally{if(is_file($tmp))unlink($tmp);}
    }
    public static function read(string $path):array {
        if(!is_file($path))throw new RuntimeException('Snapshot unavailable');
        $raw=file_get_contents($path,false,null,0,262145);
        if($raw===false||strlen($raw)>262144)throw new RuntimeException('Snapshot invalid');
        $data=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
        if(!is_array($data)||!isset($data['id'],$data['teams'],$data['published_at']))throw new RuntimeException('Snapshot schema invalid');
        $age=time()-strtotime((string)$data['published_at']);
        $data['stale']=$age<0||$age>180;
        $data['status']='unverified';
        if($data['stale'])$data['summary']='Local score snapshot delayed; current state not verified';
        return $data;
    }
}
