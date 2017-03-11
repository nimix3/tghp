<?php
function IDS_Inc($FILE="./Data/IDS.dat")
{
$hist = read_ini_file($FILE);
$ip = $_SERVER['REMOTE_ADDR'];
  if(isset($hist["$ip"]))
   {
     $hist["$ip"] += 1;
   }
  else
     $hist["$ip"] = 1;
write_ini_file($hist, $FILE);
}

function IDS_Dec($FILE="./Data/IDS.dat")
{
$hist = read_ini_file($FILE);
$ip = $_SERVER['REMOTE_ADDR'];
if(isset($hist["$ip"]))
{
$hist["$ip"] -= 1;
}
else
$hist["$ip"] = 0;
write_ini_file($hist, $FILE);
}

function IDS_Cls($FILE="./Data/IDS.dat")
{
$hist = read_ini_file($FILE);
$ip = $_SERVER['REMOTE_ADDR'];
if(isset($hist["$ip"]))
{
$hist["$ip"] = 0;
}
write_ini_file($hist, $FILE);
}

function IDS_Log($DIR="./Log/IDSLog")
{
date_default_timezone_set("Asia/Tehran");
$DATE = date('Y-m-d');
$TIME = date('H:i:s');
$ip = $_SERVER['REMOTE_ADDR'];
if(isset($_SERVER['HTTP_USER_AGENT']))
	$Agent = $_SERVER['HTTP_USER_AGENT'];
else $Agent = "";
file_put_contents($DIR."/".$DATE.".txt", $TIME.",".$ip.",".$Agent.PHP_EOL ,FILE_APPEND | LOCK_EX);
}

function IDS_Catch($Dis,$FILE="./Data/IDS.dat")
{
$hist = read_ini_file($FILE);
$ip = $_SERVER['REMOTE_ADDR'];
  if(isset($hist["$ip"]))
   {
     $hist["$ip"] += $Dis;
   }
  else
     $hist["$ip"] = $Dis;
write_ini_file($hist, $FILE);
}

function IDS_Block($IP)
{
file_put_contents(".htaccess", "deny from $IP".PHP_EOL ,FILE_APPEND | LOCK_EX);
}

function IDS_Unblock($IP)
{
	$DATA = file_get_contents(".htaccess");
	$DATA = explode(PHP_EOL,$DATA);
foreach($DATA as $Item)
{
	if(strpos($IP, $Item) !== false)
	str_replace("deny","allow",$Item);
file_put_contents(".htaccess", $Item.PHP_EOL ,FILE_APPEND | LOCK_EX);	
}
}

function IDS_Check($IP,$FILE="./Data/IDS.dat")
{
$hist = read_ini_file($FILE);
if(isset($hist["$IP"]))
	return $hist["$IP"];
return -1;
}

?>