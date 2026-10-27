<?php
declare(strict_types=1);
require_once __DIR__.'/../src/ScorerAccess.php';
$secret=str_repeat('k',40);
$token=ScorerAccess::issue($secret,'match-1','scorer-1',time()+120);
$actor=ScorerAccess::verify($secret,$token,'match-1');
if($actor['u']!=='scorer-1')throw new RuntimeException('Identity');
$failed=0;
foreach([fn()=>ScorerAccess::verify($secret,$token,'match-2'),fn()=>ScorerAccess::verify($secret,$token.'a','match-1'),fn()=>ScorerAccess::authorizeEvent($actor,['match_id'=>'match-2','revision'=>1]),fn()=>ScorerAccess::authorizeEvent($actor,['match_id'=>'match-1','revision'=>2])] as $case){try{$case();}catch(RuntimeException $e){$failed++;}}
if($failed!==4)throw new RuntimeException('Denied cases failed');
ScorerAccess::authorizeEvent($actor,['match_id'=>'match-1','revision'=>1]);
$admin=ScorerAccess::verify($secret,ScorerAccess::issue($secret,'match-1','admin-1',time()+120,'admin'),'match-1');
ScorerAccess::authorizeEvent($admin,['match_id'=>'match-1','revision'=>2]);
echo "PASS: match scope, tampering, revision permissions, admin correction\n";
