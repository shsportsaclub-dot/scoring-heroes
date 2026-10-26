<?php
declare(strict_types=1);
require_once __DIR__.'/../src/EventJournal.php';
require_once __DIR__.'/../src/BallEventReducer.php';
$path=tempnam(sys_get_temp_dir(),'sh-journal-');
try{
    $journal=new EventJournal($path);
    $event=['event_id'=>'one','sequence'=>1,'revision'=>1,'type'=>'delivery','innings'=>1,'runs'=>1,'wickets'=>0,'legal_delivery'=>true];
    $journal->append($event);
    $journal->append(array_replace($event,['revision'=>2,'runs'=>4]));
    $events=$journal->read();
    if(count($events)!==2)throw new RuntimeException('Journal history missing');
    $state=BallEventReducer::reduce($events);
    if($state['innings'][1]['runs']!==4)throw new RuntimeException('Replay correction failed');
    echo "PASS: append-only journal and revision replay\n";
}finally{unlink($path);}
