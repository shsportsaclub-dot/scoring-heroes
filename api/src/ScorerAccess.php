<?php
declare(strict_types=1);
/** Stateless match-scoped HMAC tokens. */
final class ScorerAccess {
    private static function b64(string $s):string{return rtrim(strtr(base64_encode($s),'+/','-_'),'=');}
    public static function issue(string $secret,string $matchId,string $userId,int $expiresAt,string $role='scorer'):string {
        if(strlen($secret)<32||!preg_match('/^[a-zA-Z0-9_-]{1,80}$/',$matchId)||!preg_match('/^[a-zA-Z0-9_-]{1,80}$/',$userId)||!in_array($role,['scorer','admin'],true)||$expiresAt<=time()||$expiresAt>time()+86400)throw new InvalidArgumentException('Invalid credentials');
        $body=self::b64(json_encode(['m'=>$matchId,'u'=>$userId,'r'=>$role,'exp'=>$expiresAt],JSON_THROW_ON_ERROR));
        return $body.'.'.self::b64(hash_hmac('sha256',$body,$secret,true));
    }
    public static function verify(string $secret,string $token,string $matchId):array {
        if(strlen($secret)<32||strlen($token)>2048||substr_count($token,'.')!==1)throw new RuntimeException('Unauthorized');
        [$body,$sig]=explode('.',$token,2);
        if(!hash_equals(self::b64(hash_hmac('sha256',$body,$secret,true)),$sig))throw new RuntimeException('Unauthorized');
        $decoded=base64_decode(strtr($body,'-_','+/'),true);
        if($decoded===false)throw new RuntimeException('Unauthorized');
        $data=json_decode($decoded,true);
        if(!is_array($data)||($data['m']??null)!==$matchId||!is_int($data['exp']??null)||$data['exp']<=time()||!in_array($data['r']??null,['scorer','admin'],true)||!is_string($data['u']??null)||!preg_match('/^[a-zA-Z0-9_-]{1,80}$/',$data['u']))throw new RuntimeException('Unauthorized');
        return $data;
    }
    public static function authorizeEvent(array $actor,array $event):void {
        if(($event['match_id']??null)!==($actor['m']??null))throw new RuntimeException('Match scope mismatch');
        if(($event['revision']??0)>1&&($actor['r']??null)!=='admin')throw new RuntimeException('Correction requires admin');
    }
}
