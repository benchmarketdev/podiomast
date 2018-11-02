<?php


function VerifyPhone($phone_number){
    
   
// set API Access Key
$access_key = 'a0a4a4d7fa44debc21a56fb741e8c7de';

// Initialize CURL:
$ch = curl_init('http://apilayer.net/api/validate?access_key='.$access_key.'&number='.$phone_number.'');  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($ch);
curl_close($ch);

// Decode JSON response:
$validationResult = json_decode($json, true); 
    return $validationResult;
    
}


?>
