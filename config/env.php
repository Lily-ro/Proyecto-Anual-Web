<?php
function eva_load_env(): void {
 static $loaded=false; if($loaded) return; $loaded=true;
 $envFile=dirname(__DIR__).'/.env';
 if(!file_exists($envFile)) return;
 $lines=file($envFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
 foreach($lines as $line){
  $line=trim($line);
  if($line===''||$line[0]==='#'||$line[0]===';') continue;
  if(strpos($line,'=')===false) continue;
  [$k,$v]=explode('=', $line, 2);
  $k=trim($k); $v=trim($v);
  if(strlen($v)>=2 && (($v[0]=='"'&&substr($v,-1)=='"')||($v[0]=="'"&&substr($v,-1)=="'"))) $v=substr($v,1,-1);
  if(!isset($_ENV[$k])) $_ENV[$k]=$v;
  if(!getenv($k)) putenv("$k=$v");
 }
}
function eva_env(string $key, $default=null){ eva_load_env(); $v=getenv($key); if($v!==false) return $v; return $_ENV[$key] ?? $_SERVER[$key] ?? $default; }
eva_load_env();
