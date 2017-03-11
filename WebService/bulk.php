<?php
require_once("Library/iniparser.class.php");

function BulkSend($Secret,$Numbers,$Msg,$Data,$Misc,$Agent)
{
	$tok  = substr( md5(rand()), 0, 10);
	$DBFILE = "Data/senddb.dat";
	$DBALL = "Data/sender.db";
	$DBARCH = "Data/archive.db";
	$DBFILEx = "userdata.db";
	
	$Numbers = explode(",",$Numbers);
	$Phones = array();
	foreach($Numbers as $Number)
	{
             if(isset($Number) and !empty($Number))
		$Phones["$Number"] = "*";
	}
	write_ini_file($Phones, $DBFILE.".$tok");
	unset($Numbers,$Phones);
$Msg = str_replace("\n\r",'<EOL>',$Msg);
$Msg = str_replace(PHP_EOL,'<EOL>',$Msg);
$Msg = str_replace("\r",'<EOL>',$Msg);
$Msg = str_replace("\n",'<EOL>',$Msg);
$Msg = str_replace('<EOL><EOL>','<EOL>',$Msg);
	file_put_contents($DBALL,$tok.":!:".$Msg.":!:".$Data.":!:".$Misc.":!:".$Agent.":!:".$Secret.":!:".time().PHP_EOL,FILE_APPEND);
        file_put_contents($DBARCH,$tok.":!:".$Msg.":!:".$Data.":!:".$Misc.":!:".$Agent.":!:".$Secret.":!:".time().PHP_EOL,FILE_APPEND);
		
	//$CACHE = read_ini_file($DBFILEx);
	//$CACHE["$tok"] = $Username;
	//write_ini_file($CACHE, $DBFILEx);
	
return $tok;
}

function GetReports($tok)
{
	if(file_exists("Data/senddb.dat.".$tok))
	return read_ini_file("Data/senddb.dat.".$tok);
	else return "error";
}

function GetArchive($tok)
{
	if(file_exists("Data/archive.db"))
	{
          $dat = file_get_contents("Data/archive.db");
          $dat = explode(PHP_EOL,$dat);
          foreach($dat as $dd)
          {
             $e = explode(":!:",$dd);
             if($e[0] == $tok)
             return $dd;
          }
        }
	else return "error";
}

?>