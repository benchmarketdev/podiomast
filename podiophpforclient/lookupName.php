#!/usr/bin/php -q
<?php
require 'phpagi.php';

$agi = new AGI();

//remove any non numeric characters
$no=preg_replace("#[^0-9]#","",$agi->request[agi_callerid]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://172.18.29.190/podio/test.php?phone=".$agi->request[agi_callerid]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$output = curl_exec($ch);
curl_close($ch);

if ($output){
         $name=$output;
}

else $name=$agi->request[agi_callerid];

$agi->set_variable("lookupcid", $name);

exit;

?>
