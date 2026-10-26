<?php
declare(strict_types=1);
final class ScorerEventValidator {
 public static function validate(array $event,array $history):void {
  foreach(['event_id','revision','sequence','innings','batter_id','bowler_id','bat_runs','extra_runs','legal_delivery'] as $field)if(!array_key_exists($field,$event))throw new InvalidArgumentException('Missing '.$field);
  if(($event['type']??null)!=='delivery')throw new InvalidArgumentException('Delivery required');
  foreach(['event_id','batter_id','bowler_id'] as $field)if(!is_string($event[$field])||!preg_match('/^[a-zA-Z0-9_-]{1,80}$/D',$event[$field]))throw new InvalidArgumentException('Invalid '.$field);
  foreach(['revision','sequence','innings','bat_runs','extra_runs'] as $field)if(!is_int($event[$field])||$event[$field]<($field==='bat_runs'||$field==='extra_runs'?0:1))throw new InvalidArgumentException('Invalid '.$field);
  if(!is_bool($event['legal_delivery']))throw new InvalidArgumentException('Invalid legal_delivery');
  $kind=$event['extra_type']??'none';
  if(!in_array($kind,['none','wide','no_ball','bye','leg_bye','penalty'],true))throw new InvalidArgumentException('Invalid extras');
  if(in_array($kind,['wide','no_ball'],true)&&$event['legal_delivery'])throw new InvalidArgumentException('Illegal ball marked legal');
  if($event['batter_id']===$event['bowler_id'])throw new InvalidArgumentException('Batter and bowler must differ');
  if(isset($event['wicket'])&&(!is_array($event['wicket'])||!in_array($event['wicket']['kind']??null,['bowled','caught','lbw','stumped','run_out','hit_wicket','retired_out','obstructing_field'],true)))throw new InvalidArgumentException('Invalid wicket');
  $previous=null;$maxSequence=0;
  foreach($history as $e){
   if(($e['event_id']??null)===$event['event_id']&&($previous===null||$e['revision']>$previous['revision']))$previous=$e;
   if(($e['sequence']??0)>$maxSequence)$maxSequence=$e['sequence'];
  }
  if($previous!==null){
   if($event['revision']!==$previous['revision']+1)throw new InvalidArgumentException('Correction revision must increment by one');
   if($event['sequence']!==$previous['sequence']||$event['innings']!==$previous['innings'])throw new InvalidArgumentException('Correction cannot move ball to another sequence or innings');
  }elseif($event['revision']!==1||$event['sequence']!==$maxSequence+1)throw new InvalidArgumentException('New ball requires revision 1 and next sequence');
 }
}
