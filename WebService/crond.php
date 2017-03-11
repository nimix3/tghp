<?php
require_once("Library/iniparser.class.php");
	$DBFILE = "panel/Data/senddb.dat";
	$expfile = "panel/Data/exp.db";
	$DBALL = "panel/Data/sender.db";
$MAX_MSG = 40; // message per agent at same time;
$Delay = 30;  // delay between send command;

$data = file_get_contents($DBALL);
$data = explode(PHP_EOL,$data);

if(isset($data[0]) and !empty($data[0]))
	$data = $data[0];
else exitx();

$data = explode(":!:",$data);
@ $xf = $data[0];
@ $file = $DBFILE.".".$data[0];
@ $msg = $data[1];
@ $msg = str_replace("<EOL>",PHP_EOL,$msg);
@ $misc = $data[3];
@ $agent = $data[4];
@ $ag = $agent;
@ $secret = $data[5];
@ $lastrun = $data[6];
@ $dataf = $data[2];

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
		//$robots = array_merge($robots,array("Agent1","Agent2","Agent3","Agent4","Agent5","Agent6","Agent7","Agent8","Agent9","Agent10","Agent11","Agent12","Agent13","Agent14","Agent15","Agent16","Agent17","Agent18","Agent19","Agent20","Agent21","Agent22","Agent23","Agent24","Agent25","Agent26","Agent27","Agent28","Agent29","Agent30","Agent31","Agent32","Agent33","Agent34","Agent35","Agent36","Agent37","Agent38","Agent39","Agent40","Agent41","Agent42","Agent43","Agent44","Agent45"));
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
	$datax = file_get_contents($DBALL);
	$dtt = explode(PHP_EOL,$datax)[0];
		if(explode(':!:',$dtt)[0] == $xf)
		{
			$datax = str_replace($dtt.PHP_EOL,'',$datax);
		}	
	//$msg = str_replace(PHP_EOL,"<EOL>",$msg);
	//$data = str_replace($xf.":!:".$msg.":!:".$dataf.":!:".$misc.":!:".$ag.":!:".$secret.":!:".$lastrun.PHP_EOL,"",$data);
	file_put_contents($DBALL,$datax);
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
if(count($SendPhones) >= 1)
$result2 = WebService($secret,"SendMessageSmart",implode(",",$SendPhones),$msg.PHP_EOL.PHP_EOL."Message ID : ".substr( md5(rand()), 0, 16).PHP_EOL,$dataf,$misc,$agent);
var_dump($result2);
if(isset($result2) and !empty($result2))
$result = array_merge($result1,$result2);
else 
	$result = $result1;

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
	$data = str_replace($xf.":!:".$msg.":!:".$dataf.":!:".$misc.":!:".$ag.":!:".$secret.":!:".$lastrun.PHP_EOL , $xf.":!:".$msg.":!:".$ag.":!:".$secret.":!:".time().PHP_EOL,$data);
	file_put_contents($DBALL,$data);

function WebService($Secret,$Func,$Numbers,$Msg,$Data="",$Misc="",$Agent)
{
$WEBSERVICE = "https://miogram.net/dojob.php";
		$postData = http_build_query(array(
			'Secret' => $Secret,
			'Misc' => $Misc,
			'Data' => $Data,
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