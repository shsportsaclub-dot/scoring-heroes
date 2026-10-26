<?php
declare(strict_types=1);
/** Build over groups from the latest revision of each normalized delivery. */
final class OverTimeline {
    public static function project(array $events,int $innings):array {
        if($innings<1)throw new InvalidArgumentException('Invalid innings');
        $latest=[];
        foreach($events as $e){
            if(!is_array($e)||!is_string($e['event_id']??null)||$e['event_id']===''||!is_int($e['revision']??null)||$e['revision']<1||!is_int($e['sequence']??null)||$e['sequence']<0)throw new InvalidArgumentException('Invalid event identity');
            $id=$e['event_id'];
            if(!isset($latest[$id])||$e['revision']>$latest[$id]['revision'])$latest[$id]=$e;
        }
        $ordered=array_values($latest);
        usort($ordered,static fn($a,$b)=>($a['sequence']<=>$b['sequence'])?:strcmp($a['event_id'],$b['event_id']));
        $overs=[];$legal=0;
        foreach($ordered as $e){
            if(($e['type']??null)!=='delivery'||($e['innings']??null)!==$innings)continue;
            $bat=$e['bat_runs']??null;$extra=$e['extra_runs']??null;$isLegal=$e['legal_delivery']??null;$kind=$e['extra_type']??'none';
            if(!is_int($bat)||$bat<0||!is_int($extra)||$extra<0||!is_bool($isLegal)||!in_array($kind,['none','wide','no_ball','bye','leg_bye','penalty'],true))throw new InvalidArgumentException('Invalid delivery');
            if(in_array($kind,['wide','no_ball'],true)&&$isLegal)throw new InvalidArgumentException('Invalid legal ball');
            $overNumber=intdiv($legal,6)+1;
            $key=(string)$overNumber;
            $overs[$key]??=['number'=>$key,'balls'=>[],'runs'=>0,'legal_balls'=>0,'completed'=>false];
            $label=(string)($bat+$extra);
            if($kind==='wide')$label=$extra.'wd'.($bat?'+'.$bat:'');
            elseif($kind==='no_ball')$label=($bat+$extra).'nb';
            elseif($kind==='bye')$label=$extra.'b';
            elseif($kind==='leg_bye')$label=$extra.'lb';
            if(isset($e['wicket']))$label.=' W';
            $overs[$key]['balls'][]=$label;
            $overs[$key]['runs']+=$bat+$extra;
            if($isLegal){$legal++;$overs[$key]['legal_balls']++;if($overs[$key]['legal_balls']===6)$overs[$key]['completed']=true;}
        }
        return ['overs'=>array_values($overs),'current_over'=>count($overs)?end($overs):['number'=>'1','balls'=>[],'runs'=>0,'legal_balls'=>0,'completed'=>false],'legal_balls'=>$legal,'overs_display'=>intdiv($legal,6).'.'.($legal%6)];
    }
}
