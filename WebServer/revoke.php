<?php
if($_REQUEST['tok'] == 'a2226efe5rfe')
{
	echo $out = shell_exec('sudo sh /var/www/html/terminate.sh  2>&1');
sleep(8);
	echo $out = shell_exec('sudo sh /var/www/html/revoke.sh  2>&1');	
}
echo "Error";
?>