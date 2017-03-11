<?php

function LogAccess($CNAME="default" ,$DATA="" ,$DIR="./Log/AccessLog")
{
date_default_timezone_set("Asia/Tehran");
$DATE = date('Y-m-d');
$TIME = date('H:i:s');
$ip = $_SERVER['REMOTE_ADDR'];
//if(!file_exists($DIR."/".$DATE))
	//mkdir($DIR."/".$DATE, 0775);
if(isset($_SERVER['HTTP_USER_AGENT']))
	$Agent = $_SERVER['HTTP_USER_AGENT'];
else $Agent = "";
file_put_contents($DIR."/".$CNAME."-".$DATE.".txt", $TIME.",".$ip.",".$Agent.",".$DATA.PHP_EOL ,FILE_APPEND | LOCK_EX);
}


function LogOperation($CNAME="default",$DATA,$DIR="./Log/MessagesLog")
{
date_default_timezone_set("Asia/Tehran");
$DATE = date('Y-m-d');
$TIME = date('H:i:s');
$ip = $_SERVER['REMOTE_ADDR'];
//if(!file_exists($DIR."/".$DATE))
	//mkdir($DIR."/".$DATE, 0775);
file_put_contents($DIR."/".$CNAME."-".$DATE."-Op.txt", $TIME.",".$ip.",".$DATA.PHP_EOL ,FILE_APPEND | LOCK_EX);
}

function LogMessage($CNAME="default",$DATA,$Phone,$Deliv,$DIR="./Log/MessagesLog")
{
require("/home/miogram/public_html/config.php");
@ $db = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname); // Connect to Database
@ mysqli_set_charset($db, "utf8");
date_default_timezone_set("Asia/Tehran");
$DATE = date('Y-m-d');
$TIME = date('H:i:s');
$ip = $_SERVER['REMOTE_ADDR'];
//if(!file_exists($DIR."/".$DATE))
	//mkdir($DIR."/".$DATE, 0775);
file_put_contents($DIR."/".$CNAME."-".$DATE.".txt", $TIME.",".$ip.",".$DATA.",".$Phone.PHP_EOL ,FILE_APPEND | LOCK_EX);
$sql2 = "INSERT INTO $logtable (Username,DATE,TIME,IP,DATA,Number,Delivered)
VALUES ('$CNAME', '$DATE', '$TIME', '$ip', '$DATA', '$Phone', '$Deliv')";
@ mysqli_query($db, $sql2);
@ mysqli_close($db);
}
?>