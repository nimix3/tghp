<?php
require_once("Library/iniparser.class.php");
	$DBFILE = "panel/Data/senddb.dat";
	$expfile = "panel/Data/exp.db";
	$DBALL = "panel/Data/sender.db";
$MAX_MSG = 1; // message per agent at same time;
$Delay = 30;  // delay between send command;

$data = file_get_contents($DBALL);
$datax = explode(PHP_EOL,$data);

foreach($datax as $dd){
if(isset($dd) and !empty($dd))
	$data = $dd;
else exitx();

$data = explode(":!:",$data);
@ $xf = $data[0];
@ $file = $DBFILE.".".$data[0];
@ $msg = $data[1];
@ $msg = str_replace("<EOL>",PHP_EOL,$msg);
@ $agent = $data[2];
@ $ag = $agent;
@ $secret = $data[3];
@ $lastrun = $data[4];

if(time() - $lastrun < $Delay)
	exitx();

if(isset($agent) and !empty($agent))
{
	$robots = explode(",",$agent);
}
else
{
	$robots = WebService($secret,"APIGetRobot","","","");
        $robots = $robots['Result'];
        $robots = explode(",",$robots);
}
foreach($robots as $agent){
if(file_exists($file))
{
$data = read_ini_file($file);
$exp = read_ini_file($expfile);
}
else exitx();


$SendPhones = array();
$cont = 0;

foreach($data as $numb => $res)
{
if($cont >= $MAX_MSG) break; 
	if($res == "*")
	{
		$cont++;
		$SendPhones[] = $numb;
	}
}

if($cont == 0)
{
	$data = file_get_contents($DBALL);
	$msg = str_replace(PHP_EOL,"<EOL>",$msg);
	$data = str_replace($xf.":!:".$msg.":!:".$ag.":!:".$secret.":!:".$lastrun.PHP_EOL,"",$data);
	file_put_contents($DBALL,$data);
	exitx();
}
$iii = 0;
$result1 = array();
$result2 = array();
foreach($SendPhones as $phi)
{
	if(isset($exp[$phi]) and !empty($exp[$phi]))
	{
		 unset($SendPhones[$iii]);	
		 $result1["Result"]["$phi"] = 0;
		 $result1["Status"]["$phi"] = "fail";
	}
		
	$iii++;
}

if(count($SendPhones) > 1)
$result2 = WebService($secret,"SendMessageMass",implode(",",$SendPhones),$msg,$agent);
$result = array_merge($result1,$result2);

if(isset($result) and !empty($result))
{
$result = $result["Result"];

foreach($result as $num => $stat)
{
	$data["$num"] = intval($stat);
	if(!$stat)
	{
		$exp["$num"] = time();
	}
}
write_ini_file($exp, $expfile);
write_ini_file($data, $file);
}

}

$data = file_get_contents($DBALL);
	$msg = str_replace(PHP_EOL,"<EOL>",$msg);
	$data = str_replace($xf.":!:".$msg.":!:".$ag.":!:".$secret.":!:".$lastrun.PHP_EOL , $xf.":!:".$msg.":!:".$ag.":!:".$secret.":!:".time().PHP_EOL,$data);
	file_put_contents($DBALL,$data);
sleep(15);
}

function WebService($Secret,$Func,$Numbers,$Msg,$Agent)
{
$WEBSERVICE = "https://miogram.net/dojob.php";
		$postData = http_build_query(array(
			'Secret' => $Secret,
			'Func' => $Func,
			'Message' => $Msg,
			'Robot' => $Agent,
			'Phone' => $Numbers
		));
		
		$context = stream_context_create(array(
			'http' => array(
			'method' => 'POST',
			'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
			'content' => $postData
			)));
		$response = file_get_contents($WEBSERVICE, FALSE, $context);
			if($response !== FALSE){
				$response = json_decode($response,true);
				return($response);
			}
			else return;
}

function exitx($dt="")
{
	//file_put_contents("log.txt",$dt);
	exit();
}
?>