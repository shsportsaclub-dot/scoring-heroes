<?php
declare(strict_types=1);
require_once __DIR__.'/EventJournal.php';
require_once __DIR__.'/LiveMatchProjector.php';
final class ScorerService {
    public function __construct(private string $privateDir) {}
    private function journal(string $matchId):EventJournal {
        if(!preg_match('/^[a-zA-Z0-9_-]{1,80}$/D',$matchId))throw new InvalidArgumentException('Invalid match ID');
        return new EventJournal(rtrim($this->privateDir,'/').'/'.$matchId.'.jsonl');
    }
    public function record(string $matchId,array $event):array {
        if(($event['type']??null)!=='delivery')throw new InvalidArgumentException('Only normalized deliveries accepted');
        $old=$this->journal($matchId)->read();
        // Validate entire proposed history before committing; prevents invalid event corruption.
        $new=[...$old,$event];
        LiveMatchProjector::project($new);
        $this->journal($matchId)->append($event);
        return $this->snapshot($matchId);
    }
    public function snapshot(string $matchId):array {
        $events=$this->journal($matchId)->read();
        $projection=LiveMatchProjector::project($events);
        return ['id'=>$matchId,'status'=>'scorer_preview','updated_at'=>gmdate('c'),'event_count'=>count($events),'projection'=>$projection];
    }
}
