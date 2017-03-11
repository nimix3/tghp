<?php
function Output($data)
{
header('Content-type: application/json');
echo json_encode($data);
}

function Validate($data,$which = "data")
{
if(!isset($data) or empty($data))
LogAccess($CName);
LogOperation($CName,implode("," , $REQ));
Output(array('Message' => "Error 302 : Bad Request of $which ",'Status' => "error"));
exit("302"); // Exit The WebApp
}

function getBytesFromHexString($hexdata)
{
  for($count = 0; $count < strlen($hexdata); $count+=2)
    $bytes[] = chr(hexdec(substr($hexdata, $count, 2)));

  return implode($bytes);
}

function getImageMimeType($imagedata)
{
  $imagemimetypes = array( 
    "jpeg" => "FFD8", 
    "png" => "89504E470D0A1A0A", 
    "gif" => "474946",
    "bmp" => "424D", 
    "tiff" => "4949",
    "tiff" => "4D4D"
  );

  foreach ($imagemimetypes as $mime => $hexbytes)
  {
    $bytes = getBytesFromHexString($hexbytes);
    if (substr($imagedata, 0, strlen($bytes)) == $bytes)
      return $mime;
  }

  return false;
}

function StringToArray($string,$delim="*")
{
	if($delim=="*")
	{
		  if (strpos($string, ',') !== false)
			return explode(",",$string);
	      else
			return array($string);
	}
	else
	return explode($delim,$string);
}
?>