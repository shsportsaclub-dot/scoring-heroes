<?php
declare(strict_types=1);
final class EventJournal {
    public function __construct(private string $path) {}
    public function append(array $event): void {
        if (!isset($event['event_id'],$event['revision'],$event['sequence'])) throw new InvalidArgumentException('Event identity required');
        $dir=dirname($this->path);
        if(!is_dir($dir)&&!mkdir($dir,0700,true)&&!is_dir($dir))throw new RuntimeException('Journal directory unavailable');
        $fp=fopen($this->path,'ab');if($fp===false)throw new RuntimeException('Journal unavailable');
        try {
            if(!flock($fp,LOCK_EX))throw new RuntimeException('Journal lock failed');
            $line=json_encode($event,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES)."\n";
            if(fwrite($fp,$line)!==strlen($line))throw new RuntimeException('Journal write failed');
            fflush($fp);
            flock($fp,LOCK_UN);
        } finally {fclose($fp);}
        chmod($this->path,0600);
    }
    public function read():array {
        if(!is_file($this->path))return [];
        $lines=file($this->path,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        if($lines===false)throw new RuntimeException('Journal read failed');
        return array_map(static fn(string $line):array=>json_decode($line,true,512,JSON_THROW_ON_ERROR),$lines);
    }
}
