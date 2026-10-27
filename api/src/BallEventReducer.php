<?php
declare(strict_types=1);
final class BallEventReducer {
    public static function reduce(array $events): array {
        $latest=[];
        foreach($events as $event){
            if(!is_array($event))throw new InvalidArgumentException('Invalid event');
            $id=$event['event_id']??null;$seq=$event['sequence']??null;$rev=$event['revision']??null;
            if(!is_string($id)||$id===''||!is_int($seq)||$seq<0||!is_int($rev)||$rev<1)throw new InvalidArgumentException('Missing event identity');
            if(isset($latest[$id])&&$latest[$id]['revision'] >= $rev)continue;
            $latest[$id]=$event;
        }
        $ordered=array_values($latest);
        usort($ordered,static fn(array $a,array $b):int=>($a['sequence']<=>$b['sequence'])?:strcmp($a['event_id'],$b['event_id']));
        $innings=[];
        foreach($ordered as $event){
            if(($event['type']??'')!=='delivery')continue;
            $number=$event['innings']??null;$runs=$event['runs']??null;$wickets=$event['wickets']??null;$legal=$event['legal_delivery']??null;
            if(!is_int($number)||$number<1||!is_int($runs)||$runs<0||!is_int($wickets)||$wickets<0||$wickets>2||!is_bool($legal))throw new InvalidArgumentException('Invalid delivery fields');
            if(!isset($innings[$number]))$innings[$number]=['runs'=>0,'wickets'=>0,'legal_balls'=>0,'overs'=>'0.0'];
            $innings[$number]['runs']+=$runs;
            $innings[$number]['wickets']+=$wickets;
            $innings[$number]['legal_balls']+=$legal?1:0;
        }
        ksort($innings);
        foreach($innings as &$state){$balls=$state['legal_balls'];$state['overs']=intdiv($balls,6).'.'.($balls%6);}
        unset($state);
        return ['innings'=>$innings,'events_applied'=>count($ordered)];
    }
}
