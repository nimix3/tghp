<?php
// GlobalCrypto Class Library V.1 By NIMIX3
// IV & KEY Should 32Characters Only
class GlobalCrypto
{
	public function AdvDecrypt($Cypher,$KEY,$IV)
	{
	try{
	    $KEY = base64_decode($KEY);
		$IV = base64_decode($IV);
		$Cypher = base64_decode($Cypher);			
    return (mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $KEY, $Cypher, MCRYPT_MODE_CBC, $IV));
	}
	catch(Exception $ex)
	{
	  return NULL;
	}
	}

	public function AdvEncrypt($PlainText,$KEY,$IV)
	{	
	try{
    $KEY = base64_decode($KEY);
    $IV = base64_decode($IV);
	return (base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $KEY, $PlainText, MCRYPT_MODE_CBC, $IV)));
	}
	catch(Exception $ex)
	{
	  return NULL;
	}
	}

	public function Encrypt($PlainText,$KEY)
	{
	try{
		$KEY = base64_decode($KEY);
		return base64_encode($this->SWAP(mcrypt_encrypt(MCRYPT_BLOWFISH, $KEY, $this->SWAP($this->PKCS5_Padding($PlainText)), 'ecb')));
	}
	catch(Exception $ex){
	    return NULL;
	}
	}

	public function Decrypt($Cypher,$KEY)
	{
	try{
		$Cypher = base64_decode($Cypher);
		$KEY = base64_decode($KEY);
		return $this->PKCS5_UnPadding($this->SWAP(mcrypt_decrypt(MCRYPT_BLOWFISH, $KEY, $this->SWAP($Cypher), 'ecb')));
	}
	catch(Exception $ex){
		return NULL;
	}
	}

	private function PKCS7_UnPadding($value)
	{//removes PKCS7 padding
    $blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $packing = ord($value[strlen($value) - 1]);
    if($packing && $packing < $blockSize)
    {
        for($P = strlen($value) - 1; $P >= strlen($value) - $packing; $P--)
        {
            if(ord($value{$P}) != $packing)
            {
                $packing = 0;
            }
        }
    }	
    return substr($value, 0, strlen($value) - $packing); 
	}

	private function PKCS7_Padding($value)	{
	//Adds PKCS7 padding
	$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $len = strlen($value);
    $padding = $block - ($len % $block);
    $value .= str_repeat(chr($padding),$padding);
	return $value;
	}

	private function PKCS5_Padding($data) {
		$padlen = 8-(strlen($data) % 8);
		for ($i=0; $i<$padlen; $i++)
		$data .= chr($padlen);
		return $data; 
	}
	

	private function PKCS5_UnPadding($data) {
		$padlen = ord(substr($data, strlen($data)-1, 1));
		if ($padlen>8)
		return $data;
		
		for ($i=strlen($data)-$padlen; $i<strlen($data); $i++) {
			if (ord(substr($data, $i, 1)) != $padlen)
			return false;  
		}
		
		return substr($data, 0, strlen($data)-$padlen); 
	}

	private function SWAP($data) {
	//Swaps byte order (little-endian <-> big-endian)
		$res="";
		for ($i=0; $i<strlen($data); $i+=4) {
			list(,$val) = unpack('N', substr($data, $i, 4));
			$res .= pack('V', $val); 
		}
		
		return $res; 
	}

}
?>