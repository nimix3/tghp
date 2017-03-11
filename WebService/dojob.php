<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once("config.php");
require_once("bulk.php");
require_once("Library/IDS.class.php");
require_once("Library/iniparser.class.php");
require_once("Library/logger.class.php");
require_once("Library/misc.class.php");
//=====================================================================//
$db = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname); // Connect to Database
if (!$db) {
die("Connection failed: " . mysqli_connect_error()); }
@ mysqli_set_charset($db, "utf8");
//=======================REQUEST METHOD CHECK==========================//
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
	unset($_REQUEST);
	IDS_Catch(30); //Discourage 30x the attacker
	IDS_Log();
	Output(array('Message' => "Bad Request",'Status' => "error",'Code' => "812"));
	exitX($db); // Exit The WebApp
}
if(IDS_Check($_SERVER['REMOTE_ADDR']) > 100)
{
	  IDS_Block($_SERVER['REMOTE_ADDR']);
	  Output(array('Message' => "Session Blocked",'Status' => "error",'Code' => "815"));
	  exitX($db); // Exit The WebApp
}
//===========================GET REQUEST DATA==========================//
$REQ = $_POST;
if(!isset($_POST["Secret"]) or empty($_POST["Secret"]))
{Output(array('Message' => "Empty Secret",'Status' => "error",'Code' => "800")); exitX($db); IDS_Catch(30); IDS_Log();}

@ $Secret = Secure($_POST["Secret"]);
$IP = $_SERVER['REMOTE_ADDR'];
unset($_REQUEST); // For More Security
@ $Func = $REQ["Func"];
@ $Phone = $REQ["Phone"];
@ $Message = $REQ["Message"];
@ $Data = $REQ["Data"];
@ $Misc = $REQ["Misc"];
//=======================GET DATA FROM DATABASE========================//
$Secret = mysqli_real_escape_string($db,$Secret);
$sql = "SELECT Username,IP,Mcred,Pcred,Vcred,Fcred,Ocred,Mlimit,Plimit,Vlimit,Flimit,canGet,isSuper,robot,Ads,Active FROM $table WHERE Secret = '$Secret' LIMIT 1";
@ $result = mysqli_query($db,$sql);
if(mysqli_num_rows($result) <= 0)
{Output(array('Message' => "Invalid Secret",'Status' => "error",'Code' => "801")); exitX($db); IDS_Catch(30); IDS_Log();}
$data = mysqli_fetch_assoc($result);
@ $MsgCredit = $data["Mcred"];
@ $PhotoCredit = $data["Pcred"];
@ $VideoCredit = $data["Vcred"];
@ $FileCredit = $data["Fcred"];
@ $OtherCredit = $data["Ocred"];
@ $canGet = $data["canGet"];
@ $isSuper = $data["isSuper"];
@ $Ads = $data["Ads"];
@ $Active = $data["Active"];
@ $MsgMax += strlen($Ads);

if(!isset($canGet) or empty($canGet))
	$canGet = 0;
if(!isset($isSuper) or empty($isSuper))
	$isSuper = 0;

if($Active); else {Output(array('Message' => "Account Not Activated",'Status' => "error",'Code' => "826")); exitX($db); IDS_Log();}

if(isset($Ads) and !empty($Ads))
	$Message .= "
$Ads";

@ $PhotoMax = ($data["Plimit"] * 1024 * 1024); // To Byte
@ $VideoMax = ($data["Vlimit"] * 1024 * 1024); // To Byte
@ $FileMax = ($data["Flimit"] * 1024 * 1024); // To Byte
@ $MsgMax = $data["Mlimit"]; // Characters

@ $TheIP = $data["IP"];
@ $Username = $data["Username"];

@ $Robot = $data["robot"];
@ $Robxo = $Robot;

if(!isset($Robot) or empty($Robot))
{Output(array('Message' => "No Robot Has Been Set Yet",'Status' => "error",'Code' => "827")); exitX($db);}

$Robot = explode(",",$Robot);
$Robot = array_merge($Robot,array("Agent1","Agent2","Agent3","Agent4","Agent5","Agent6","Agent7","Agent8","Agent9","Agent10","Agent11","Agent12","Agent13","Agent14","Agent15","Agent16","Agent17","Agent18","Agent19","Agent20","Agent21","Agent22","Agent23","Agent24","Agent25","Agent26","Agent27","Agent28","Agent29","Agent30","Agent31","Agent32","Agent33","Agent34","Agent35","Agent36","Agent37","Agent38","Agent39","Agent40","Agent41","Agent42","Agent43","Agent44","Agent45","Stack1","Stack2"));
$Rbt = array(); // List of Your Available Robots
$rdat = read_ini_file("./robots.dat"); //List Of Available Robots
foreach ($Robot as $robo)
{
	@ $Rbt["$robo"] = $rdat["$robo"];
}
@ $r = $REQ["Robot"];
if(isset($r) and isset($Rbt["$r"]))
{
$SrvTok = $Rbt["$r"];	
$Robot = $r;
}
else
{
$SrvTok = $rdat[$Robot[0]];	
$Robot = $Robot[0];
}

if(stripos($Robot,"stack") !== false)
{
		$canGet = 1;
		$isSuper = 1;
}

if($TheIP != "*")
   if(strpos($TheIP, $IP) === false)
{Output(array('Message' => "Invalid IP Address",'Status' => "error",'Code' => "802")); exitX($db); IDS_Catch(10); IDS_Log();}
LogAccess($Username,$Username."|".$IP);
LogOperation($Username,implode("," , $REQ));
//==============================Lets Play==============================//
switch ($Func) {
//////////////////////////////////////////////////
case 'SetStatusOnline':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaGetAdmin':
if($canGet)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaGetMember':
if($canGet)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaInfo':
if($canGet)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaLeave':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaList':
if($canGet)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaJoin':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaUpgrade':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaLinkGen':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'ChatLinkGen':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaLinkJoin':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'ChatLinkJoin':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'FindUsername':
if($canGet)
	echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaInvite':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc,$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaKick':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc,$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaSetAbout':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc,"","",$Data);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaSetUsername':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc,"","",$Data);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaCreate':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc,"","",$Data);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaRename':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc,"","",$Data);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaSendMessage':
if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Message Length is Too High",'Status' => "error",'Code' => "304")); exitX($db);}
if($MsgCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,"",$Message);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$MsgCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
LogMessage($Username,$Message,$Misc,$Misc);
@ mysqli_query($db, $sql);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'MetaSetAdmin':
if($isSuper)
	echo API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'DeleteMessage':
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc,"","","");
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'GetHistoryX':
if($canGet)
echo API($SrvTok,$Username,$Func,$Misc,"","","100,0");
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'FindMessageID':
if($canGet)
echo API($SrvTok,$Username,$Func,$Misc,"","","");
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaSetPhoto':
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc,'','',$Data);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MetaSendPhoto':
if(strlen(base64_decode($Data)) <= $PhotoMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($PhotoCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,"","",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$PhotoCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Misc,$Misc);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'MetaSendVideo':
if(strlen(base64_decode($Data)) <= $VideoMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($VideoCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,"","",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$VideoCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Misc,$Misc);
}
echo $res; exitX($db); 
break;
//////////////////////////////////////////////////
case 'MetaSendFile':
if(strlen(base64_decode($Data)) <= $FileMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($FileCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,"","",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$FileCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Misc,$Misc);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'MetaSendDocument':
if(strlen(base64_decode($Data)) <= $FileMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($FileCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,"","",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$FileCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Misc,$Misc);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendTyping':
if($isSuper)
	echo API($SrvTok,$Username,$Func,"",$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'SendMessage':
if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Message Length is Too High",'Status' => "error",'Code' => "304")); exitX($db);}
if($MsgCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,"",$Phone,$Message);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$MsgCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
LogMessage($Username,$Message,$Phone,$Phone);
@ mysqli_query($db, $sql);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendMessageX':
if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Message Length is Too High",'Status' => "error",'Code' => "304")); exitX($db);}
if($MsgCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,"",$Message);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$MsgCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
LogMessage($Username,$Message,$Misc,$Misc);
@ mysqli_query($db, $sql);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendMessageWhatsApp':
if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Message Length is Too High",'Status' => "error",'Code' => "304")); exitX($db);}
if($MsgCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,"",$Phone,$Message);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$MsgCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
LogMessage($Username,$Message,$Phone,$Phone);
@ mysqli_query($db, $sql);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendMessageMass':
$PNum = StringToArray($Phone);
if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Message Length is Too High",'Status' => "error",'Code' => "304")); exitX($db);}
if($MsgCredit >= count($PNum)); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,"",$Phone,$Message);
    $resX = json_decode($res,true);
	if(is_array($resX["Result"]))
	foreach($resX["Result"] as $Numb)
	{
		if($Numb)
		$MsgCredit -= 1;
	}
	else if($resX["Result"]) $MsgCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Message,$Phone,Delivered($resX["Result"]));
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendMessageGP':
$PNum = StringToArray($Phone);
if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Message Length is Too High",'Status' => "error",'Code' => "304")); exitX($db);}
if($MsgCredit >= count($PNum)); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,$Message,$Data);
    $resX = json_decode($res,true);
	if(is_array($resX["Result"]))
	foreach($resX["Result"] as $Numb)
	{
		if($Numb)
		$MsgCredit -= 1;
	}
	else if($resX["Result"]) $MsgCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Message,$Phone,Delivered($resX["Result"]));
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendMessageSmart':
$PNum = StringToArray($Phone);
if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Message Length is Too High",'Status' => "error",'Code' => "304")); exitX($db);}
if($MsgCredit >= count($PNum)); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,$Message,$Data);
    $resX = json_decode($res,true);
	if(is_array($resX["Result"]))
	foreach($resX["Result"] as $Numb)
	{
		if($Numb)
		$MsgCredit -= 1;
	}
	else if($resX["Result"]) $MsgCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Message,$Phone,Delivered($resX["Result"]));
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendMessageWhatsAppMass':
$PNum = StringToArray($Phone);
if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Message Length is Too High",'Status' => "error",'Code' => "304")); exitX($db);}
if($MsgCredit >= count($PNum)); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,"",$Phone,$Message);
    $resX = json_decode($res,true);
	//if(is_array($resX["Result"]))
	foreach($PNum as $Numb)
	{
		//if($Numb)
		$MsgCredit -= 1;
	}
	//else if($resX["Result"]) $MsgCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Message,$Phone,$Phone);
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'ChatSetPhoto':
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc,'','',$Data);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'CreateGroupChat':
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc,$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'ChatInfo':
if($canGet)
echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'RenameChat':
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'ChatAddUser':
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc,$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'ChatDeleteUser':
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc,$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'SetProfileName':
if(stripos($Robot,"stack") !== false)
	{Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'BlockUser':
if(stripos($Robot,"stack") !== false)
	{Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
if($isSuper)
echo API($SrvTok,$Username,$Func,"",$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'UnBlockUser':
if($isSuper)
echo API($SrvTok,$Username,$Func,"",$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'MarkRead':
if($isSuper)
echo API($SrvTok,$Username,$Func,"",$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'GetUserInfo':
if($canGet)
echo API($SrvTok,$Username,$Func,"",$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'GetDialogList':
if($canGet)
echo API($SrvTok,$Username,$Func);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'GetHistory':
if($canGet)
echo API($SrvTok,$Username,$Func,$Misc,$Phone);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'SetUsername':
if(stripos($Robot,"stack") !== false)
	{Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'SetProfilePhoto':
if(stripos($Robot,"stack") !== false)
	{Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
if($isSuper)
echo API($SrvTok,$Username,$Func,$Misc,"","",$Data);
else {Output(array('Message' => "Permission Denied",'Status' => "error",'Code' => "309")); exitX($db);}
break;
//////////////////////////////////////////////////
case 'SendPhoto':
if(strlen(base64_decode($Data)) <= $PhotoMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($PhotoCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$PhotoCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Phone,$Phone);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendVideo':
if(strlen(base64_decode($Data)) <= $VideoMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($VideoCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$VideoCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Phone,$Phone);
}
echo $res; exitX($db); 
break;
//////////////////////////////////////////////////
case 'SendAudio':
if(strlen(base64_decode($Data)) <= $FileMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($FileCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$FileCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Phone,$Phone);
}
	
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendText':
if(strlen(base64_decode($Data)) <= $FileMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($FileCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$FileCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Phone,$Phone);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendFile':
if(strlen(base64_decode($Data)) <= $FileMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($FileCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$FileCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Phone,$Phone);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendDocument':
if(strlen(base64_decode($Data)) <= $FileMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($FileCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$FileCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Phone,$Phone);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendPhotoMass':
$PNum = StringToArray($Phone);
if(strlen(base64_decode($Data)) <= $PhotoMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($PhotoCredit >= count($PNum)); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
    $resX = json_decode($res,true);
	if(is_array($resX["Result"]))
	foreach($resX["Result"] as $Numb)
	{
		if($Numb)
		$PhotoCredit -= 1;
	}
	else if($resX["Result"]) $PhotoCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Phone,Delivered($resX["Result"]));
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendVideoMass':
$PNum = StringToArray($Phone);
if(strlen(base64_decode($Data)) <= $VideoMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($VideoCredit >= count($PNum)); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
    $resX = json_decode($res,true);
	if(is_array($resX["Result"]))
	foreach($resX["Result"] as $Numb)
	{
		if($Numb)
		$VideoCredit -= 1;
	}
	else if($resX["Result"]) $VideoCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Phone,Delivered($resX["Result"]));
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendFileMass':
$PNum = StringToArray($Phone);
if(strlen(base64_decode($Data)) <= $FileMax); else {Output(array('Message' => "File Size is Too High",'Status' => "error",'Code' => "303")); exitX($db);}
if($FileCredit >= count($PNum)); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone,"",$Data);
    $resX = json_decode($res,true);
	if(is_array($resX["Result"]))
	foreach($resX["Result"] as $Numb)
	{
		if($Numb)
		$FileCredit -= 1;
	}
	else if($resX["Result"]) $FileCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Data,$Phone,Delivered($resX["Result"]));
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendLocation':
if($OtherCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$OtherCredit -= 1;	
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);	
LogMessage($Username,$Misc,$Phone,$Phone);
}

echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'SendContact':
if($OtherCredit >= 0); else {Output(array('Message' => "No Credit",'Status' => "error",'Code' => "305")); exitX($db);}
	$res = API($SrvTok,$Username,$Func,$Misc,$Phone);
    $resX = json_decode($res,true);
if($resX["Result"])
{
$OtherCredit -= 1;
$sql = "UPDATE $table SET Mcred='$MsgCredit',Pcred='$PhotoCredit',Vcred='$VideoCredit',Fcred='$FileCredit',Ocred='$OtherCredit' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
LogMessage($Username,$Misc,$Phone,$Phone);
}
echo $res; exitX($db);
break;
//////////////////////////////////////////////////
case 'APIGetLimits':
Output(array('Result' => array('Message' => $MsgMax,'Photo' => $PhotoMax,'Video' => $VideoMax,'File' => $FileMax , 'canGet' => $canGet,'isSuper' => $isSuper),'Status' => "checking")); exitX($db);
break;
//////////////////////////////////////////////////
case 'APIGetCredits':
Output(array('Result' => array('Message' => $MsgCredit,'Photo' => $PhotoCredit,'Video' => $VideoCredit,'File' => $FileCredit,'Other' => $OtherCredit),'Status' => "checking")); exitX($db);
break;
//////////////////////////////////////////////////
case 'APIGetIP':
Output(array('Result' => $IP)); exitX($db);
break;
//////////////////////////////////////////////////
case 'APIChangeSecret':
$newsec = substr( md5(rand()), 0, 10);
$sql = "UPDATE $table SET Secret='$newsec' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
mysqli_close($db);
Output(array('Result' => $newsec,'Message' => "Secret Changed",'Status' => "success",'Code' => "407")); exitX($db);
break;
//////////////////////////////////////////////////
case 'APISetIP':
if(isset($Misc) and !empty($Misc)); else {Output(array('Message' => "No Input",'Status' => "error",'Code' => "330")); exitX($db);}
if($Misc != "*")
$Misc = $Misc.",".$_SERVER['SERVER_ADDR'];
$sql = "UPDATE $table SET IP='$Misc' WHERE Secret = '$Secret' LIMIT 1";
@ mysqli_query($db, $sql);
mysqli_close($db);
Output(array('Result' => $Misc,'Message' => "Allow IP Sets",'Status' => "success",'Code' => "409")); exitX($db);
break;
//////////////////////////////////////////////////
case 'APIGetRobot':
Output(array('Result' => $data["robot"])); exitX($db);
break;
//////////////////////////////////////////////////
case 'APIGetActive':
Output(array('Result' => $Active)); exitX($db);
break;
//////////////////////////////////////////////////
case 'APIGetUsername':
Output(array('Result' => $Username)); exitX($db);
break;
//////////////////////////////////////////////////
case 'APIisSuper':
Output(array('Result' => $isSuper)); exitX($db);
break;
//////////////////////////////////////////////////
case 'APIcanGet':
Output(array('Result' => $canGet)); exitX($db);
break;
//////////////////////////////////////////////////
case 'APIGetAds':
if(isset($Ads) and !empty($Ads))
{Output(array('Result' => true)); exitX($db);}
else
{Output(array('Result' => false)); exitX($db);}
break;
//////////////////////////////////////////////////
case 'APIGetReport':
if(!isset($Misc) or empty($Misc))
	$Misc = 1000;
if($Misc > 1000)
	$Misc = 1000;
$sql = "SELECT Username,DATE,TIME,IP,DATA,Number,Delivered FROM $logtable WHERE Username = '$Username'";
@ $result = mysqli_query($db,$sql);
if(mysqli_num_rows($result) <= 0)
{Output(array('Message' => "No DATA",'Status' => "error",'Code' => "111")); exitX($db);}
$data = array();
$ix = 0;
while ($row = mysqli_fetch_assoc($result) and $ix < 1000) {
    array_push($data,$row);
	$ix++;
}
$data = array_reverse($data, true);
$data = array_slice($data,0,$Misc);
Output($data);
exitX($db);
break;
//////////////////////////////////////////////////
default:
	Output(array('Message' => "Bad Request of Functions",'Status' => "error",'Code' => "302"));
    exitX($db);
break;
}
//=======================OTHER FUNCTIONS NEED========================//
function API($ServerToken="",$Username="",$Func="",$Misc="",$Number="",$Message="",$Data="",$Addr="http://107.181.172.245/api.php")
{
	$API = $Addr;
		$postData = http_build_query(array(
			'UserID' => $Username,
			'ServerToken' => $ServerToken,
			'Func' => $Func,
			'Misc' => $Misc,
			'Data' => $Data,
			'Message' => $Message,
			'Phone' => $Number
		));
		
			$context = stream_context_create(array(
			'http' => array(
			'method' => 'POST',
			'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
			'content' => $postData
			)));
			$response = file_get_contents($API, FALSE, $context);
			if($response !== FALSE){
			return $response;
			} 
			else
			{Output(array('Message' => "No Response From Server",'Status' => "error",'Code' => "999")); exitX($db);}	
}
function Delivered($Numbers)
{
$Result = array();
	foreach($Numbers as $numb => $val)
	{
		if($val)
		array_push($Result,$numb);
	}
return implode(",",$Result);
}
function Secure($str)
{
$str = str_replace('(','',$str);
$str = str_replace(')','',$str);
$str = str_replace('[','',$str);
$str = str_replace(']','',$str);
$str = str_replace('{','',$str);
$str = str_replace('}','',$str);
$str = str_replace('*','',$str);
$str = str_replace('?','',$str);
$str = str_replace('!','',$str);
$str = str_replace(';','',$str);
$str = str_replace('&','',$str);
$str = str_replace('%','',$str);
$str = str_replace('-','',$str);
$str = str_replace('_','',$str);
$str = str_replace('=','',$str);
$str = str_replace('|','',$str);
$str = str_replace('<','',$str);
$str = str_replace('>','',$str);
$str = str_replace('/','',$str);
$str = str_replace('\\','',$str);
$str = str_replace('+','',$str);
$str = str_replace('.','',$str);
return $str;
}
function exitX($db)
{
@ mysqli_close($db);
	exit();
}
//==============================Finalizing=============================//
mysqli_close($db); // Close Connection
?>