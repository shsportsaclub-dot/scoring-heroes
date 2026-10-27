<?php
declare(strict_types=1);
$source=file_get_contents(__DIR__.'/../public/public-local-view.php');
if($source===false)throw new RuntimeException('Public feed source missing');
if(!str_contains($source,"getenv('LOCAL_MATCH_PUBLIC_ENABLED')!=='1'"))throw new RuntimeException('Public feed must default to disabled');
if(!str_contains($source,"['GET','HEAD']"))throw new RuntimeException('Public feed must be read-only');
if(!str_contains($source,"PublicSnapshot::read"))throw new RuntimeException('Public feed must read a published snapshot');
echo "PASS: public feed source guardrails\n";
