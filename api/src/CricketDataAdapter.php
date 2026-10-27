<?php
declare(strict_types=1);
final class CricketDataAdapter {
    public function __construct(private string $apiKey, private string $baseUrl='https://api.cricapi.com/v1') {
        if($apiKey==='') throw new InvalidArgumentException('Missing CRICKETDATA_API_KEY');
        if(!str_starts_with($baseUrl,'https://')) throw new InvalidArgumentException('HTTPS required');
    }
    public function currentMatches(int $offset=0): array {
        if($offset<0||$offset>10000)throw new InvalidArgumentException('Invalid offset');
        $url=rtrim($this->baseUrl,'/').'/currentMatches?'.http_build_query(['apikey'=>$this->apiKey,'offset'=>$offset]);
        $ch=curl_init($url);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HTTPHEADER=>['Accept: application/json']]);
        $raw=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$error=curl_error($ch);curl_close($ch);
        if($raw===false||$status!==200) throw new RuntimeException('Provider unavailable (HTTP '.$status.'): '.($error!==''?'network error':'provider error'));
        $json=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
        if(!is_array($json)||!isset($json['data'])||!is_array($json['data'])) throw new RuntimeException('Unexpected provider schema');
        return array_values(array_filter(array_map([$this,'normalize'],$json['data'])));
    }
    private function normalize(mixed $m): ?array {
        if(!is_array($m)||empty($m['id'])) return null;
        $names=is_array($m['teams']??null)?$m['teams']:[];
        $scores=is_array($m['score']??null)?$m['score']:[];
        $teamInfo=is_array($m['teamInfo']??null)?$m['teamInfo']:[];
        $teams=[];
        foreach(array_slice($names,0,2) as $i=>$name){
            $entry=$scores[$i]??[];
            $display='—';
            if(is_array($entry)&&isset($entry['r'],$entry['w'])) $display=(string)$entry['r'].'/'.(string)$entry['w'];
            $logo=null;
            foreach($teamInfo as $info){if(is_array($info)&&strcasecmp((string)($info['name']??''),(string)$name)===0&&is_string($info['img']??null)&&str_starts_with($info['img'],'https://')){$logo=$info['img'];break;}}
            $teams[]=['name'=>(string)$name,'score'=>$display,'logo_url'=>$logo];
        }
        if(count($teams)<2)return null;
        $started=($m['matchStarted']??false)===true;
        $ended=($m['matchEnded']??false)===true;
        $status=$ended?'result':($started?'live':'upcoming');
        return ['id'=>(string)$m['id'],'title'=>(string)($m['name']??'Cricket Match'),'teams'=>$teams,'summary'=>(string)($m['status']??''),'status'=>$status,'events'=>[],'scorecard'=>[],'source'=>'cricketdata','match_start'=>(string)($m['dateTimeGMT']??''),'provider_score_updated_at'=>isset($m['scoreUpdatedAt'])?(string)$m['scoreUpdatedAt']:null,'updated_at'=>gmdate('c')];
    }
}
