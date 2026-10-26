<?php
declare(strict_types=1);
/**
 * Build batter and bowler figures from normalized delivery events.
 * Input runs split into bat_runs and extras; extras have explicit type.
 * Run-outs do not automatically credit a bowler.
 */
final class ScorecardProjector {
    public static function project(array $events): array {
        $latest=[];
        foreach($events as $e){
            if(!is_array($e)||!is_string($e['event_id']??null)||!is_int($e['revision']??null)||!is_int($e['sequence']??null))throw new InvalidArgumentException('Invalid event identity');
            if(!isset($latest[$e['event_id']])||$e['revision']>$latest[$e['event_id']]['revision'])$latest[$e['event_id']]=$e;
        }
        $list=array_values($latest);
        usort($list,static fn($a,$b)=>($a['sequence']<=>$b['sequence'])?:strcmp($a['event_id'],$b['event_id']));
        $batters=[];$bowlers=[];$innings=[];
        foreach($list as $e){
            if(($e['type']??null)!=='delivery')continue;
            foreach(['innings','bat_runs','extra_runs','batter_id','bowler_id','legal_delivery'] as $key)if(!array_key_exists($key,$e))throw new InvalidArgumentException('Missing '.$key);
            $n=$e['innings'];$bat=$e['bat_runs'];$extra=$e['extra_runs'];$b=$e['batter_id'];$p=$e['bowler_id'];$legal=$e['legal_delivery'];$kind=$e['extra_type']??'none';
            if(!is_int($n)||$n<1||!is_int($bat)||$bat<0||!is_int($extra)||$extra<0||!is_string($b)||$b===''||!is_string($p)||$p===''||!is_bool($legal)||!in_array($kind,['none','wide','no_ball','bye','leg_bye','penalty'],true))throw new InvalidArgumentException('Invalid delivery');
            if(($kind==='wide'||$kind==='no_ball')&&$legal)throw new InvalidArgumentException('Illegal ball marked legal');
            if(!isset($innings[$n]))$innings[$n]=['runs'=>0,'wickets'=>0,'legal_balls'=>0,'extras'=>0];
            $innings[$n]['runs']+=$bat+$extra;$innings[$n]['extras']+=$extra;$innings[$n]['legal_balls']+=(int)$legal;
            $bk=$n.':'.$b;$pk=$n.':'.$p;
            $batters[$bk]??=['innings'=>$n,'id'=>$b,'runs'=>0,'balls'=>0,'fours'=>0,'sixes'=>0];
            $bowlers[$pk]??=['innings'=>$n,'id'=>$p,'runs'=>0,'wickets'=>0,'legal_balls'=>0];
            $batters[$bk]['runs']+=$bat;
            $batters[$bk]['balls']+=($kind==='wide'?0:1);
            $batters[$bk]['fours']+=($bat===4?1:0);$batters[$bk]['sixes']+=($bat===6?1:0);
            $bowlers[$pk]['legal_balls']+=(int)$legal;
            $bowlers[$pk]['runs']+=$bat+($kind==='bye'||$kind==='leg_bye'||$kind==='penalty'?0:$extra);
            $wicket=$e['wicket']??null;
            if($wicket!==null){
                if(!is_array($wicket)||!in_array($wicket['kind']??null,['bowled','caught','lbw','stumped','run_out','hit_wicket','retired_out','obstructing_field'],true))throw new InvalidArgumentException('Invalid wicket');
                $innings[$n]['wickets']++;
                if(in_array($wicket['kind'],['bowled','caught','lbw','stumped','hit_wicket'],true))$bowlers[$pk]['wickets']++;
            }
        }
        foreach($innings as &$v){$v['overs']=intdiv($v['legal_balls'],6).'.'.($v['legal_balls']%6);}unset($v);
        foreach($batters as &$v){$v['strike_rate']=$v['balls']?round($v['runs']*100/$v['balls'],2):null;}unset($v);
        foreach($bowlers as &$v){$v['overs']=intdiv($v['legal_balls'],6).'.'.($v['legal_balls']%6);$v['economy']=$v['legal_balls']?round($v['runs']*6/$v['legal_balls'],2):null;}unset($v);
        ksort($innings);
        return ['innings'=>$innings,'batters'=>array_values($batters),'bowlers'=>array_values($bowlers)];
    }
}
