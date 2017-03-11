<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once("Library/IDS.class.php");
require_once("Library/iniparser.class.php");
require_once("Library/logger.class.php");
require_once("Library/Telegram.php");
require_once("Library/misc.class.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
	unset($_REQUEST);
	IDS_Catch(30); //Discourage 30x the attacker
	IDS_Log();
	if(IDS_Check($_SERVER['REMOTE_ADDR']) > 100)
{
	IDS_Block($_SERVER['REMOTE_ADDR']);
	Output(array('Message' => "Error 815 : Session Blocked",'Status' => "error"));
	exit(); // Exit The WebApp
}
	Output(array('Message' => "Error 812 : Bad Request",'Status' => "error"));
	exit(); // Exit The WebApp
}
//==================DATA From API=======================//
$REQ = $_POST;
@ $UserID = $_POST["UserID"];
@ $ServerToken = $_POST["ServerToken"];
$IP = $_SERVER['REMOTE_ADDR'];
unset($_REQUEST); // For More Security

@ $Func = $REQ["Func"];
@ $Phone = $REQ["Phone"];
@ $Message = $REQ["Message"];
@ $Data = $REQ["Data"];
@ $Misc = $REQ["Misc"];
//==================DATA From Config====================//
$dat = read_ini_file("./Data/Servers.dat");
if(!isset($dat["$ServerToken"])) // Fisrt Check Server Token
{
	IDS_Catch(30); //Discourage 30x the attacker
	IDS_Log();
	Output(array('Message' => "Error 813 : Invalid Token",'Status' => "error"));
	exit(); // Exit The WebApp
}
$dat = explode("|#|",$dat["$ServerToken"]);

@ $AllowIP = $dat[0];
@ $PORT = $dat[1];
@ $CName = $dat[2];
@ $CEmail = $dat[3];
@ $CPhone = $dat[4];
@ $WhatsAppAgent = $dat[5];
@ $WhatsAppPass = $dat[6];

$dat = read_ini_file("./Data/Credits.dat");
if(isset($dat["$ServerToken"]))
$dat = explode(",",$dat["$ServerToken"]);
else
 $dat = array(100,25,0,0,0,0,0);

@ $MsgCredit = $dat[0];
@ $PhotoCredit = $dat[1];
@ $VideoCredit = $dat[2];
@ $FileCredit = $dat[3];
@ $OtherCredit = $dat[4];
@ $CanGet = $dat[5];
@ $isSuper = $dat[6];

$dat = read_ini_file("./Data/Limits.dat");
if(isset($dat["$ServerToken"]))
$dat = explode(",",$dat["$ServerToken"]);
else
 $dat = array(100,2,4,1);

@ $PhotoMax = ($dat[1] * 1024 * 1024); // To Byte
@ $VideoMax = ($dat[2] * 1024 * 1024); // To Byte
@ $FileMax = ($dat[3] * 1024 * 1024); // To Byte
@ $MsgMax = $dat[0]; // Characters


//====================Misc======================//
if($IP != $AllowIP)// Fisrt Check Remote IP
if($AllowIP != "*")
{
	IDS_Catch(30); //Discourage 30x the attacker
	IDS_Log();
	Output(array('Message' => "Error 814 : Invalid IP Address",'Status' => "error"));
	exit(); // Exit The WebApp
}

if(IDS_Check($IP) > 100)
{
	IDS_Block($IP);
	IDS_Log();
	Output(array('Message' => "Error 815 : Session Blocked",'Status' => "error"));
	exit(); // Exit The WebApp
}
//==================Validation==================//
if(!isset($UserID))
{
	LogAccess($CName,$UserID);
	LogOperation($CName,implode("," , $REQ));
	Output(array('Message' => "Error 301 : No UserID",'Status' => "error"));
	exit();
}
LogAccess($CName,$UserID);
LogOperation($CName,implode("," , $REQ));
global $Conn;
@ $Conn = new Telegram('tcp://0.0.0.0:'.$PORT);

//==================Lets Play===================//
switch ($Func) {
//////////////////////////////////////////////////
case 'SetStatusOnline':
if($Misc=="Online") 
{
	if($Conn->setStatusOnline()) 
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit(0);}
}
else if($Misc=="Offline")
{
	if($Conn->setStatusOffline()) 
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SendTyping':
if(isset($Phone) and !empty($Phone)) 
{
	if($Conn->sendTyping(NumberToPeerX($Conn,$Phone,$UserID)))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SendMessage':
if(isset($Phone) and !empty($Phone) and isset($Message) and !empty($Message)) 
{
 if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Error 304 : Message Length is High",'Status' => "error")); exit();}
 if($MsgCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); exit();}

	if($Conn->msg(NumberToPeerX($Conn,$Phone,$UserID),$Message))
	{Output(array('Result' => true,'Status' => "success")); CredDec(0,$ServerToken); LogMessage($CName,$UserID.":".$Message); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit("302");}
//////////////////////////////////////////////////
case 'SendMessageX':
if(isset($Misc) and !empty($Misc) and isset($Message) and !empty($Message)) 
{
 if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Error 304 : Message Length is High",'Status' => "error")); exit();}
 if($MsgCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); exit();}

	if($Conn->msg($Misc,$Message))
	{Output(array('Result' => true,'Status' => "success")); CredDec(0,$ServerToken); LogMessage($CName,$UserID.":".$Message); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit("302");}
//////////////////////////////////////////////////
case 'SendMessageWhatsApp':
if(isset($Phone) and !empty($Phone) and isset($Message) and !empty($Message)) 
{
 if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Error 304 : Message Length is High",'Status' => "error")); exit();}
 if($MsgCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); exit();}

	if($WhatsAppAgent == "WA" or $WhatsAppPass =="WP")
		{Output(array('Message' => "Error 101 : WhatsApp Not Supported",'Status' => "error")); exit();}
	$Phone = str_replace("+","",$Phone);
	$out = shell_exec('sudo /usr/src/wa/yowsup-master/yowsup-cli demos -s '.$Phone.' "'.$Message.'"'.' --login '.$WhatsAppAgent.':'.$WhatsAppPass.' 2>&1');
	{Output(array('Result' => true,'Status' => "sent")); CredDec(0,$ServerToken); LogMessage($CName,$UserID.":".$Message); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit("302");}
//////////////////////////////////////////////////
case 'SendMessageWhatsAppMass':
if(isset($Phone) and !empty($Phone) and isset($Message) and !empty($Message)) 
{
    $Phone = StringToArray($Phone);
	if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Error 304 : Message Length is High",'Status' => "error")); exit();}
	if($MsgCredit >= count($Phone)); else {Output(array('Message' => "Error 306 : Low Credit",'Status' => "error")); exit();}
    $resultx = array();
	if($WhatsAppAgent == "WA" or $WhatsAppPass =="WP")
		{Output(array('Message' => "Error 101 : WhatsApp Not Supported",'Status' => "error")); exit();}
	
	foreach($Phone as $ph)
	{
		$ph = str_replace("+","",$ph);
	shell_exec('sudo yowsup-cli demos -s '.$ph.' "'.$Message.'"'.' --login '.$WhatsAppAgent.':'.$WhatsAppPass);
	CredDec(0,$ServerToken);
	LogMessage($CName,$UserID.":".$Message);
	}
	
	{Output(array('Result' => true,'Status' => "sent")); exit();}
	
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit("302");}
//////////////////////////////////////////////////
case 'SendMessageMass':
if(isset($Phone) and !empty($Phone) and isset($Message) and !empty($Message)) 
{   
    $Phone = StringToArray($Phone);
	if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Error 304 : Message Length is High",'Status' => "error")); exit();}
	if($MsgCredit >= count($Phone)); else {Output(array('Message' => "Error 306 : Low Credit",'Status' => "error")); exit();}
	$resultx = array();
	foreach($Phone as $numb)
	{
	if($peer = NumberToPeer($Conn,$numb,$UserID))
	{
	if($Conn->msg($peer,$Message))
	{CredDec(0,$ServerToken); LogMessage($CName,$UserID.":".$Message); $resultx["$numb"] = true;}
	else
	{$resultx["$numb"] = false;}
	}
	else {$resultx["$numb"] = false;}
	}
	{Output(array('Result' => $resultx,'Status' => "checking")); unset($resultx); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'CreateGroupChat':
if(isset($Phone) and !empty($Phone) and isset($Misc) and !empty($Misc)) 
{
	$users = array();
	$Phone = StringToArray($Phone);
	foreach($Phone as $numb)
	{
		if($peer=NumberToPeer($Conn,$numb,$UserID))
		$users["$numb"] = $peer;
	}
	if($Conn->createGroupChat($Misc,$users))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'ChatInfo':
if(isset($Misc) and !empty($Misc)) 
{
    if($res = $Conn->chatInfo($Misc))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit("302");}
//////////////////////////////////////////////////
case 'RenameChat':
if(isset($Misc) and !empty($Misc)) 
{
	$Misc = StringToArray($Misc);
	if($Conn->renameChat($Misc[0],$Misc[1]))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}	
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'ChatAddUser':
if(isset($Phone) and !empty($Phone) and isset($Misc) and !empty($Misc)) 
{
	$Misc = StringToArray($Misc);
	if($Conn->chatAddUser($Misc[0],NumberToPeer($Conn,$Phone,$UserID),$Misc[1]))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'ChatDeleteUser':
if(isset($Phone) and !empty($Phone) and isset($Misc) and !empty($Misc)) 
{
	if($Conn->chatDeleteUser($Misc,NumberToPeer($Conn,$Phone,$UserID)))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SetProfileName':
if(isset($Misc) and !empty($Misc)) 
{
	$Misc = StringToArray($Misc);
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->setProfileName($Misc[0],$Misc[1]))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'BlockUser':
if(isset($Phone) and !empty($Phone)) 
{
	if($Conn->blockUser(NumberToPeer($Conn,$Phone,$UserID)))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'UnBlockUser':
if(isset($Phone) and !empty($Phone)) 
{
	if($Conn->unblockUser(NumberToPeer($Conn,$Phone,$UserID)))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MarkRead':
if(isset($Phone) and !empty($Phone)) 
{
	if($Conn->markRead(NumberToPeer($Conn,$Phone,$UserID)))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'GetUserInfo':
if(isset($Phone) and !empty($Phone)) 
{
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->getUserInfo(NumberToPeer($Conn,$Phone,$UserID)))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'GetDialogList':
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->getDialogList())
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
//////////////////////////////////////////////////
case 'GetHistory':
if(isset($Phone) and !empty($Phone))
{
	$Misc = StringToArray($Misc);
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->getHistory(NumberToPeer($Conn,$Phone,$UserID),$Misc[0],$Misc[1]))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'GetHistoryX':
if(isset($Misc) and !empty($Misc))
{
	$Data = StringToArray($Data);
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->getHistory($Misc,$Data[0],$Data[1]))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'FindMessageID':
if(isset($Misc) and !empty($Misc))
{
	$Misc = StringToArray($Misc);
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->getHistory($Misc[0],500,0))
	{
			$res = json_decode(json_encode($res),true);
			$res = array_reverse($res);
			foreach($res as $resss)
			{
					if($resss['from']['peer_id'].$resss['date'] == $Misc[1])
					{
						Output(array('Result' => $resss['id'],'Status' => "success"));
						exit;
					}
			}
		{Output(array('Result' => false,'Status' => "fail")); exit();}	
	}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SetUsername':
if(isset($Misc) and !empty($Misc)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->setUsername($Misc))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaGetAdmin':
if(isset($Misc) and !empty($Misc)) 
{
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->MetaGetAdmin($Misc))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaGetMember':
if(isset($Misc) and !empty($Misc)) 
{
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->MetaGetMember($Misc))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaInfo':
if(isset($Misc) and !empty($Misc)) 
{
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->MetaInfo($Misc))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaLeave':
if(isset($Misc) and !empty($Misc)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaLeave($Misc))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaList':
if(isset($Misc) and !empty($Misc)) 
{
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->MetaList($Misc))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaJoin':
if(isset($Misc) and !empty($Misc)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaJoin($Misc))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaUpgrade':
if(isset($Misc) and !empty($Misc)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaUpgrade($Misc))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaLinkGen':
if(isset($Misc) and !empty($Misc)) 
{
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->MetaLinkGen($Misc))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'ChatLinkGen':
if(isset($Misc) and !empty($Misc)) 
{
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->ChatLinkGen($Misc))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaLinkJoin':
if(isset($Misc) and !empty($Misc)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaLinkJoin($Misc))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'ChatLinkJoin':
if(isset($Misc) and !empty($Misc)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->ChatLinkJoin($Misc))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'FindUsername':
if(isset($Misc) and !empty($Misc)) 
{
	if($CanGet); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($res = $Conn->FindUsername($Misc))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaInvite':
if(isset($Misc) and !empty($Misc) and isset($Phone) and !empty($Phone)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaInvite($Misc,NumberToPeerX($Conn,$Phone,$UserID)))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaKick':
if(isset($Misc) and !empty($Misc) and isset($Phone) and !empty($Phone)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaKick($Misc,NumberToPeerX($Conn,$Phone,$UserID)))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaSetAbout':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaSetAbout($Misc,$Data))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaSetUsername':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaSetUsername($Misc,$Data))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaCreate':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaCreate($Misc,$Data))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaRename':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaRename($Misc,$Data))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaSendMessage':
if(isset($Misc) and !empty($Misc) and isset($Message) and !empty($Message)) 
{
 if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Error 304 : Message Length is High",'Status' => "error")); exit();}
 if($MsgCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); exit();}

	if($Conn->MetaSendMessage($Misc,$Message))
	{Output(array('Result' => true,'Status' => "success")); CredDec(0,$ServerToken); LogMessage($CName,$UserID.":".$Message); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit("302");}
//////////////////////////////////////////////////
case 'MetaSetAdmin':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data) and isset($Phone) and !empty($Phone)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->MetaSetAdmin($Misc,NumberToPeerX($Conn,$Phone,$UserID),$Data))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaSetPhoto':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($file = Base64ToFile($Data,($Misc?"jpg":"jpg"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if($Conn->MetaSetPhoto($Misc,$file))
	{Output(array('Result' => true,'Status' => "success")); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'MetaSendPhoto':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data))  
{
	@ $Misc = explode(",",$Misc);
	if(!isset($Misc[1]) or empty($Misc[1])) { $Misc[1] = " "; }
	if($file = Base64ToFile($Data,($Misc?"jpg":"jpg"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $PhotoMax); else {Output(array('Message' => "Error 304 : Photo Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($PhotoCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->MetaSendPhoto($Misc[0],$file,$Misc[1]))
	{Output(array('Result' => true,'Status' => "success")); CredDec(1,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'MetaSendVideo':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data))  
{
	@ $Misc = explode(",",$Misc);
	if(!isset($Misc[1]) or empty($Misc[1])) { $Misc[1] = " "; }
	if($file = Base64ToFile($Data,($Misc?"mp4":"mp4"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $VideoMax); else {Output(array('Message' => "Error 304 : Video Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($VideoCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->MetaSendVideo($Misc[0],$file,$Misc[1]))
	{Output(array('Result' => true,'Status' => "success")); CredDec(2,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'MetaSendFile':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data))  
{
	@ $Misc = explode(",",$Misc);
	if($file = Base64ToFile($Data,($Misc[1]?$Misc[1]:"zip"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $FileMax); else {Output(array('Message' => "Error 304 : File Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($FileCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->MetaSendFile($Misc[0],$file))
	{Output(array('Result' => true,'Status' => "success")); CredDec(3,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'MetaSendDocument':
if(isset($Misc) and !empty($Misc) and isset($Data) and !empty($Data)) 
{
	@ $Misc = explode(",",$Misc);
	if($file = Base64ToFile($Data,($Misc[1]?$Misc[1]:"doc"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $FileMax); else {Output(array('Message' => "Error 304 : Document Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($FileCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->MetaSendDocument($Misc[0],$file))
	{Output(array('Result' => true,'Status' => "success")); CredDec(3,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'SetProfilePhoto':
if(isset($Data) and !empty($Data)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($file = Base64ToFile($Data,($Misc?$Misc:"jpg"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if($Conn->setProfilePhoto($file))
	{Output(array('Result' => true,'Status' => "success")); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'DeleteMessage':
if(isset($Misc) and !empty($Misc)) 
{
	if($isSuper); else {Output(array('Message' => "Error 309 : Permission Denied",'Status' => "error")); exit();}
	if($Conn->DelMsg($Misc))
	{Output(array('Result' => true,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SendPhoto':
if(isset($Phone) and !empty($Phone) and isset($Data) and !empty($Data))  
{
	if($file = Base64ToFile($Data,($Misc?$Misc:"jpg"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $PhotoMax); else {Output(array('Message' => "Error 304 : Photo Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($PhotoCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->sendPhoto(NumberToPeerX($Conn,$Phone,$UserID),$file))
	{Output(array('Result' => true,'Status' => "success")); CredDec(1,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'SendVideo':
if(isset($Phone) and !empty($Phone) and isset($Data) and !empty($Data))  
{
	if($file = Base64ToFile($Data,($Misc?$Misc:"mp4"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $VideoMax); else {Output(array('Message' => "Error 304 : Video Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($VideoCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->sendVideo(NumberToPeerX($Conn,$Phone,$UserID),$file))
	{Output(array('Result' => true,'Status' => "success")); CredDec(2,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'SendAudio':
if(isset($Phone) and !empty($Phone) and isset($Data) and !empty($Data))  
{
	if($file = Base64ToFile($Data,($Misc?$Misc:"mp3"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $FileMax); else {Output(array('Message' => "Error 304 : Audio Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($FileCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->sendAudio(NumberToPeerX($Conn,$Phone,$UserID),$file))
	{Output(array('Result' => true,'Status' => "success")); CredDec(3,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'SendText':
if(isset($Phone) and !empty($Phone) and isset($Data) and !empty($Data))  
{
	if($file = Base64ToFile($Data,($Misc?$Misc:"txt"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $FileMax); else {Output(array('Message' => "Error 304 : Text Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($FileCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->sendText(NumberToPeerX($Conn,$Phone,$UserID),$file))
	{Output(array('Result' => true,'Status' => "success")); CredDec(3,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'SendFile':
if(isset($Phone) and !empty($Phone) and isset($Data) and !empty($Data))  
{
	if($file = Base64ToFile($Data,($Misc?$Misc:"zip"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $FileMax); else {Output(array('Message' => "Error 304 : File Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($FileCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->sendFile(NumberToPeerX($Conn,$Phone,$UserID),$file))
	{Output(array('Result' => true,'Status' => "success")); CredDec(3,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'SendDocument':
if(isset($Phone) and !empty($Phone) and isset($Data) and !empty($Data)) 
{
	if($file = Base64ToFile($Data,($Misc?$Misc:"doc"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $FileMax); else {Output(array('Message' => "Error 304 : Document Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($FileCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); @ unlink($file); exit();}

	if($Conn->sendDocument(NumberToPeerX($Conn,$Phone,$UserID),$file))
	{Output(array('Result' => true,'Status' => "success")); CredDec(3,$ServerToken); LogMessage($CName,$UserID.":".$Data); @ unlink($file); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}	
//////////////////////////////////////////////////
case 'SendPhotoMass':
if(isset($Phone) and !empty($Phone) and isset($Data) and !empty($Data)) 
{
	$Phone = StringToArray($Phone);
	if($file = Base64ToFile($Data,($Misc?$Misc:"jpg"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $PhotoMax); else {Output(array('Message' => "Error 304 : Photo Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($PhotoCredit >= count($Phone)); else {Output(array('Message' => "Error 306 : Low Credit",'Status' => "error")); @ unlink($file); exit();}
	$resultx = array();
	foreach($Phone as $numb)
	{
	if($peer = NumberToPeer($Conn,$numb,$UserID))
	{
	if($Conn->sendPhoto($peer,$file))
	{CredDec(1,$ServerToken); LogMessage($CName,$UserID.":".$Message); $resultx["$numb"] = true;}
	else
	{$resultx["$numb"] = false;}
	}
	else {$resultx["$numb"] = false;}
	}
	{Output(array('Result' => $resultx,'Status' => "checking")); unset($resultx); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SendVideoMass':
if(isset($Phone) and !empty($Phone) and isset($Data) and !empty($Data)) 
{
	$Phone = StringToArray($Phone);
	if($file = Base64ToFile($Data,($Misc?$Misc:"mp4"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $VideoMax); else {Output(array('Message' => "Error 304 : Video Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($VideoCredit >= count($Phone)); else {Output(array('Message' => "Error 306 : Low Credit",'Status' => "error")); @ unlink($file); exit();}
	$resultx = array();
	foreach($Phone as $numb)
	{
	if($peer = NumberToPeer($Conn,$numb,$UserID))
	{
	if($Conn->sendVideo($peer,$file))
	{CredDec(2,$ServerToken); LogMessage($CName,$UserID.":".$Message); $resultx["$numb"] = true;}
	else
	{$resultx["$numb"] = false;}
	}
	else {$resultx["$numb"] = false;}
	}
	{Output(array('Result' => $resultx,'Status' => "checking")); unset($resultx); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SendFileMass':
if(isset($Phone) and !empty($Phone) and isset($Data) and !empty($Data)) 
{
	$Phone = StringToArray($Phone);
	if($file = Base64ToFile($Data,($Misc?$Misc:"zip"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
	if(filesize($file) <= $FileMax); else {Output(array('Message' => "Error 304 : File Size is High",'Status' => "error")); @ unlink($file); exit();}
    if($FileCredit >= count($Phone)); else {Output(array('Message' => "Error 306 : Low Credit",'Status' => "error")); @ unlink($file); exit();}
	$resultx = array();
	foreach($Phone as $numb)
	{
	if($peer = NumberToPeer($Conn,$numb,$UserID))
	{
	if($Conn->sendFile($peer,$file))
	{CredDec(3,$ServerToken); LogMessage($CName,$UserID.":".$Message); $resultx["$numb"] = true;}
	else
	{$resultx["$numb"] = false;}
	}
	else {$resultx["$numb"] = false;}
	}
	{Output(array('Result' => $resultx,'Status' => "checking")); unset($resultx); @ unlink($file); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SendLocation':
if(isset($Phone) and !empty($Phone) and isset($Misc) and !empty($Misc)) 
{
 if($OtherCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); exit();}
$Misc = StringToArray($Misc);
	if($Conn->sendLocation(NumberToPeerX($Conn,$Phone,$UserID),$Misc[0],$Misc[1]))
	{Output(array('Result' => true,'Status' => "success")); CredDec(4,$ServerToken); LogMessage($CName,$UserID.":".$Misc[0]." ".$Misc[1]); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SendContact':
if(isset($Phone) and !empty($Phone) and isset($Misc) and !empty($Misc)) 
{
 if($OtherCredit >= 0); else {Output(array('Message' => "Error 305 : No Credit",'Status' => "error")); exit();}
$Misc = StringToArray($Misc);
	if($Conn->sendContact(NumberToPeerX($Conn,$Phone,$UserID),$Phone,$Misc[0],$Misc[1]))
	{Output(array('Result' => true,'Status' => "success")); CredDec(4,$ServerToken); LogMessage($CName,$UserID.":".$Misc[0]." ".$Misc[1]." ".$Phone); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'ChatSetPhoto':
if(isset($Data) and !empty($Data) and isset($Misc) and !empty($Misc))
{
	if($file = Base64ToFile($Data,($Misc?"jpg":"jpg"))); else {Output(array('Message' => "Error 400 : Not Valid File",'Status' => "error")); exit();}
    if($res = $Conn->chatSetPhoto($Misc,$file))
	{Output(array('Result' => $res,'Status' => "success")); exit();}
	else
	{Output(array('Result' => false,'Status' => "fail")); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SendMessageGP':
if(isset($Phone) and !empty($Phone) and isset($Message) and !empty($Message)) 
{   
    $Phone = StringToArray($Phone);
	if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Error 304 : Message Length is High",'Status' => "error")); exit();}
	if($MsgCredit >= count($Phone)); else {Output(array('Message' => "Error 306 : Low Credit",'Status' => "error")); exit();}
	$resultx = array();
		//
		@ $GPx = explode("::",$Message);
		@ $GP = $GPx[0];
		@ $Message = $GPx[1];
		if($Conn->createGroupChat($GP,array(NumberToPeer($Conn,'+989215312213',$UserID)))); else {Output(array('Result' => false,'Status' => "fail")); exit();}
		if($file = Base64ToFile($Data,($Misc?$Misc:"jpg"))) 
		if($Conn->sendFile($GP,$file)){sleep(3);} else {Output(array('Result' => false,'Status' => "fail")); exit();}
		if($Conn->msg($GP,$Message)); else {Output(array('Result' => false,'Status' => "fail")); exit();}
		//
	foreach($Phone as $numb)
	{
	if($peer = NumberToPeer($Conn,$numb,$UserID))
	{
	if($Conn->chatAddUser($GP,$peer,4))
	{$Conn->chatDeleteUser($GP,$peer); CredDec(0,$ServerToken); LogMessage($CName,$UserID.":".$Message); $resultx["$numb"] = true;}
	else
	{$resultx["$numb"] = false;}
	}
	else {$resultx["$numb"] = false;}
	}
	{Output(array('Result' => $resultx,'Status' => "checking")); unset($resultx); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'SendMessageSmart':
if(isset($Phone) and !empty($Phone) and isset($Message) and !empty($Message)) 
{   
    $Phone = StringToArray($Phone);
	if(strlen($Message) <= $MsgMax); else {Output(array('Message' => "Error 304 : Message Length is High",'Status' => "error")); exit();}
	if($MsgCredit >= count($Phone)); else {Output(array('Message' => "Error 306 : Low Credit",'Status' => "error")); exit();}
	$resultx = array();
		//
		@ $GPx = explode("::",$Message);
		@ $GP = $GPx[0];
		@ $Message = $GPx[1];
		@ $About = "https://MioGram.net/";//$GPx[1];
		if($Conn->MetaCreate($GP,$About)); else {Output(array('Result' => false,'Status' => "fail")); exit();}
		if($file = Base64ToFile($Data,($Misc?$Misc:"jpg"))) 
		if($Conn->MetaSendFile($GP,$file)){sleep(1);} else {Output(array('Result' => false,'Status' => "fail")); exit();}
		if($Conn->MetaSendMessage($GP,$Message)); else {Output(array('Result' => false,'Status' => "fail")); exit();}
		//
	foreach($Phone as $numb)
	{
	if($peer = NumberToPeer($Conn,$numb,$UserID))
	{
	if($Conn->MetaInvite($GP,$peer))
	{CredDec(0,$ServerToken); LogMessage($CName,$UserID.":".$Message); $resultx["$numb"] = true;}
	else
	{$resultx["$numb"] = false;}
	}
	else {$resultx["$numb"] = false;}
	}
	{Output(array('Result' => $resultx,'Status' => "checking")); unset($resultx); exit();}
}
else
{Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error")); exit();}
//////////////////////////////////////////////////
case 'APIGetLimits':
APIGetLimits();
//////////////////////////////////////////////////
case 'APIGetCredits':
APIGetCredits();
//////////////////////////////////////////////////
default:
	LogAccess($CName,$UserID);
	LogOperation($CName,implode("," , $REQ));
	Output(array('Message' => "Error 302 : Bad Request of Func ",'Status' => "error"));
    exit();
	//endswitch;
}


//===================Func====================//
function NumberToPeer($Conn,$number,$User="default")
{
	@ $Conn->deleteContact($number." ".$User);
	sleep(1);
	if($Conn->addContact($number, $number, $User))
		return $number." ".$User;
	return false;
}

function NumberToPeerX($Conn,$number,$User="default")
{
if($peer = NumberToPeer($Conn,$number,$User)); else {Output(array('Result' => false, 'Message' => "Number Not Exist or Wrong",'Status' => "error")); exit();}
return $peer;
}

function CredDec($i,$ServerToken)
{
$dat = read_ini_file("./Data/Credits.dat");
if(isset($dat["$ServerToken"]))
$cred = explode(",",$dat["$ServerToken"]);
else
 $dat = array(100,25,0,0,0,0,0);
	$cred[$i] -= 1;
	$dat["$ServerToken"]=implode(",",$cred);
write_ini_file($dat, "./Data/Credits.dat");
}

function Base64ToFile($data,$type="jpg")
{
$FILE = substr( md5(rand()), 0, 8);
if (base64_decode($data, true)) {
    @ $ifp = fopen("/var/www/html/tmp/".$FILE.".".$type, "wb");
    if (strpos($data, ',') !== false)
	{
	$data = explode(',', $data);
    @ fwrite($ifp, base64_decode($data[1]));
	}
	else
    @ fwrite($ifp, base64_decode($data)); 
    @ fclose($ifp);
return "/var/www/html/tmp/".$FILE.".".$type;	
} else {
return false;
}
}

function APIGetLimits()
{
$dat = read_ini_file("./Data/Limits.dat");
if(!isset($dat["$ServerToken"]))
$dat = explode(",",$dat["$ServerToken"]);
else
 $dat = array(100,2,4,0);
Output(array('Result' => $dat,'Status' => "checking")); exit();
}

function APIGetCredits()
{
$dat = read_ini_file("./Data/Credits.dat");
if(!isset($dat["$ServerToken"]))
$dat = explode(",",$dat["$ServerToken"]);
else
 $dat = array(100,25,0,0,0,0,0);
Output(array('Result' => $dat,'Status' => "checking")); exit();
}

function APIGetUserID()
{
Output(array('Result' => $UserID,'Status' => "checking")); exit();
}


?>