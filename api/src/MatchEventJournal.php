<?php
declare(strict_types=1);
final class MatchEventJournal {
    public function __construct(private string $directory) {
        if($directory===''||$directory[0]!=='/')throw new InvalidArgumentException('Absolute directory required');
    }
    private function path(string $matchId):string {
        if(!preg_match('/^[a-zA-Z0-9_-]{1,80}$/',$matchId))throw new InvalidArgumentException('Invalid match ID');
        return rtrim($this->directory,'/').'/'.$matchId.'.jsonl';
    }
    public function append(string $matchId,array $event,string $actorId):array {
        $path=$this->path($matchId);
        if(!is_dir($this->directory)&&!mkdir($this->directory,0700,true)&&!is_dir($this->directory))throw new RuntimeException('Journal directory unavailable');
        $fp=fopen($path,'c+');if($fp===false)throw new RuntimeException('Journal unavailable');
        try{
            if(!flock($fp,LOCK_EX))throw new RuntimeException('Lock unavailable');
            rewind($fp);$latest=null;$maxSequence=-1;
            while(($line=fgets($fp))!==false){
                $existing=json_decode($line,true,512,JSON_THROW_ON_ERROR);
                if(($existing['event_id']??null)===$event['event_id']&&($latest===null||$existing['revision']>$latest['revision']))$latest=$existing;
                if(($existing['event_id']??null)!==$event['event_id'])$maxSequence=max($maxSequence,(int)($existing['sequence']??-1));
            }
            $revision=$event['revision'];
            if($latest!==null){
                if($revision===$latest['revision']){
                    $compare=$latest;unset($compare['audit']);
                    $incoming=$event;unset($incoming['audit']);
                    if($compare==$incoming)return ['accepted'=>true,'duplicate'=>true];
                    throw new DomainException('Conflicting duplicate revision');
                }
                if($revision!==$latest['revision']+1||$event['sequence']!==$latest['sequence'])throw new DomainException('Invalid revision transition');
            }elseif($revision!==1||$event['sequence']<=$maxSequence)throw new DomainException('Invalid new event sequence');
            $event['audit']=['actor'=>$actorId,'received_at'=>gmdate('c')];
            $line=json_encode($event,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES)."\n";
            fseek($fp,0,SEEK_END);
            if(fwrite($fp,$line)!==strlen($line)||!fflush($fp))throw new RuntimeException('Journal append failed');
            return ['accepted'=>true,'duplicate'=>false];
        }finally{flock($fp,LOCK_UN);fclose($fp);}
    }
    public function read(string $matchId):array {
        $path=$this->path($matchId);
        if(!is_file($path))return [];
        $fp=fopen($path,'rb');if($fp===false)throw new RuntimeException('Journal unavailable');
        try{
            if(!flock($fp,LOCK_SH))throw new RuntimeException('Lock unavailable');
            $events=[];
            while(($line=fgets($fp))!==false)$events[]=json_decode($line,true,512,JSON_THROW_ON_ERROR);
            return $events;
        }finally{flock($fp,LOCK_UN);fclose($fp);}
    }
}
