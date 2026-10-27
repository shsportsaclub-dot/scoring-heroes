<?php
declare(strict_types=1);
final class MatchStore {
    private string $path;
    public function __construct(string $path) {$this->path=$path;}
    public function read(): array {
        if (!is_file($this->path)) return ['updated_at'=>null,'matches'=>[]];
        $raw=file_get_contents($this->path);
        $data=json_decode($raw === false ? '' : $raw,true);
        if (!is_array($data)||!isset($data['matches'])||!is_array($data['matches'])) throw new RuntimeException('Invalid match cache');
        return $data;
    }
    public function write(array $matches): void {
        $dir=dirname($this->path);
        if (!is_dir($dir) && !mkdir($dir,0700,true) && !is_dir($dir)) throw new RuntimeException('Cannot create cache directory');
        $payload=json_encode(['updated_at'=>gmdate('c'),'matches'=>$matches],JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
        $tmp=tempnam($dir,'match-');
        if($tmp===false) throw new RuntimeException('Cannot allocate cache file');
        try {
            if(file_put_contents($tmp,$payload,LOCK_EX)===false) throw new RuntimeException('Cache write failed');
            chmod($tmp,0600);
            if(!rename($tmp,$this->path)) throw new RuntimeException('Cache swap failed');
        } finally {if(is_file($tmp)) unlink($tmp);}
    }
}
