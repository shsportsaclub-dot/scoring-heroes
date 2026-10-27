<?php
declare(strict_types=1);
/**
 * Operational preflight; run from CLI with deployment environment variables.
 * No secrets or filesystem paths are printed.
 */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$checks=[
    'PHP >= 8.1'=>PHP_VERSION_ID>=80100,
    'JSON extension'=>extension_loaded('json'),
    'Scorer ingest token >= 32 chars'=>strlen((string)getenv('SCORER_INGEST_TOKEN'))>=32,
    'Journal path absolute'=>str_starts_with((string)getenv('EVENT_JOURNAL_PATH'),'/'),
    'Snapshot path absolute'=>str_starts_with((string)getenv('PUBLIC_SNAPSHOT_PATH'),'/'),
    'Public feed explicitly enabled'=>getenv('LOCAL_MATCH_PUBLIC_ENABLED')==='1',
];
$journal=(string)getenv('EVENT_JOURNAL_PATH');
$snapshot=(string)getenv('PUBLIC_SNAPSHOT_PATH');
$checks['Journal and snapshot distinct']=$journal!==''&&$snapshot!==''&&$journal!==$snapshot;
$webRoot=realpath(__DIR__.'/../public');
foreach(['Journal outside public root'=>$journal,'Snapshot outside public root'=>$snapshot] as $label=>$path){
    $parent=realpath(dirname($path));
    $checks[$label]=$parent!==false&&$webRoot!==false&&!($parent===$webRoot||str_starts_with($parent,$webRoot.DIRECTORY_SEPARATOR));
}
$fail=false;
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL').': '.$name.PHP_EOL;if(!$ok)$fail=true;}
exit($fail?1:0);
