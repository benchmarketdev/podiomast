<?php

/**
 * Basic Class for dealing with PODIO apps/items
 * Author : ABDRABAH Rafik
 * Wizz it 2017
 */
ini_set('memory_limit', '-1');

require_once 'PodioAPI.php';

class PODIOManeger
{
    
private $client_id;
private $client_secret;

    
public function __construct($client_id,$client_secret) {
    Podio::set_debug(true);
    $this->client_id = $client_id;
    $this->client_secret = $client_secret;
  
  }
    
    
/**
 * Authenticate handler for PODIO app authentication flow.
 * @param  string $app_token             App access token
 * @param  string $app_id                App id
 * @return boolean
 */
    
private function Authenticate($app_id,$app_token){
    
Podio::setup($this->client_id, $this->client_secret);
Podio::authenticate_with_app($app_id, $app_token);
    
}
  
    
   
/**
 * Gets All Podio  app items ( Phone numbers and names ) .
 * @param   string    $NameItem_externalID   Name item's external id
 * @param   string    $PhoneItem_externalID  Phone number item's external id
 * @param   string    $app_id                App id
 * @param   string    $app_token             App access token
 * @param   array     $options               Podio Item filter options
 * @return  Items in array
 */
    
private function getAllItems($NameItem_externalID,$PhoneItem_externalID,$app_id,$app_token,$options){
    $this->Authenticate($app_id,$app_token);
    
    
    
    $accounts_items = PodioItem::filter($app_id,$options);
    $accounts_all_phones="";
    $accounts_data=array();
    foreach( $accounts_items as $acc_itm){

        if ($acc_itm->fields[$PhoneItem_externalID]){
                $values = array_map('array_pop',$acc_itm->fields[$PhoneItem_externalID]->values);
                $imploded = implode(',', $values);
      
                $tmp=array("name"=>$acc_itm->fields[$NameItem_externalID]->values,"phones"=>$imploded,"testphone"=>$acc_itm->fields[$PhoneItem_externalID]->values,"id"=>$acc_itm->item_id);
                array_push($accounts_data,$tmp );
                $accounts_all_phones=$accounts_all_phones.$imploded.',';
        
          }
     }   
    return array('data'=>$accounts_data,'items'=>$accounts_items);
}
    
    
 
/**
 * Searchs for Caller's name providing his phone number .
 * @param   string $PhoneNumber           Phone Number
 * @param   string $Accounts_app_id       Companies App id
 * @param   string $Accounts_app_token    Companies App access token
 * @param   string $People_app_id         People App id
 * @param   string $People_app_token      People App access token
 * @param   array  $options               Podio Item filter options
 * @return  Callers name if found , false if not
 */

private function SearchCaller($PhoneNumber,$A_NameItem_externalID,$A_PhoneItem_externalID,$P_NameItem_externalID,$P_PhoneItem_externalID,$Accounts_app_id,$Accounts_app_token,$People_app_id,$People_app_token,$options){
        
         

         $found=false;
         $Name="";
         $k="";
                $this->Authenticate($People_app_id,$People_app_token);


            $itemsCount=PodioItem::get_count( $People_app_id );
            $tempcount=0;
   
            while (  ($options["offset"]<$itemsCount) and $found==false ) {
                
               
         
              
            
            $people_all=$this->getAllItems($P_NameItem_externalID,$P_PhoneItem_externalID,$People_app_id,$People_app_token,$options);
           
                $tempcount = count($people_all['data']);
                $options["offset"]=$options["offset"]+$tempcount ;
                
            $dataset=$people_all['items'];
            $people_data=$people_all['data'];
           
               
                 foreach ($people_data as $people ) {

            if((strlen($PhoneNumber)<=strlen($people["phones"])) and strlen($PhoneNumber)>7) {
                if(strstr( $people["phones"],$PhoneNumber )){
                    foreach($people["testphone"] as $ph) {
                       
         if((strlen($PhoneNumber)<=strlen($ph["value"])) and strlen($PhoneNumber)>7) {
                if(strstr( $ph["value"],$PhoneNumber ))
                    $k=$ph['type'];
                    } 
         }
                    $result=array("name"=>$people["name"],"linetype"=>$k,"id"=>$people['id']);
                    $Name= $people["name"]." (".$k.")";
                    $found=true;
                    break;
                   } 

         }else
            if((strlen($PhoneNumber)>=strlen($people["phones"])) and strlen($PhoneNumber)<14) 
                if(strstr( $PhoneNumber,$people["phones"] )) {
                     foreach($people["testphone"] as $ph) {
                        if((strlen($PhoneNumber)<=strlen($ph["value"])) and strlen($PhoneNumber)>7) {
                if(strstr( $ph["value"],$PhoneNumber ))
                    $k=$ph['type'];
                    } 
                    } 
                    $result=array("name"=>$people["name"],"linetype"=>$k,"id"=>$people['id']);
                    $Name=$people["name"]." (".$k.")";
                    $found=true;
                    break;
                } 
        }
                
 
        
       


            }
    
    
     if ($found==false){
               $this->Authenticate( $Accounts_app_id,$Accounts_app_token);
               $itemsCount=PodioItem::get_count( $Accounts_app_id );
                $tempcount=0;s
                 $options["offset"]=0;
     
            while (  ($options["offset"]<$itemsCount) and $found==false ) {
                
  
            $people_all=$this->getAllItems($A_NameItem_externalID,$A_PhoneItem_externalID,$Accounts_app_id,$Accounts_app_token,$options);
                $tempcount = count($people_all['data']);
                $options["offset"]=$options["offset"]+$tempcount ;
            $dataset=$people_all['items'];
            $accounts_data=$people_all['data'];
            
             
            
            foreach ($accounts_data as $accounts ) {
                if((strlen($PhoneNumber)<=strlen($accounts["phones"])) and strlen($PhoneNumber)>7) {
                    if(strstr( $accounts["phones"],$PhoneNumber )){
                         foreach($accounts["testphone"] as $ph) {
                        if((strlen($PhoneNumber)<=strlen($ph["value"])) and strlen($PhoneNumber)>7) {
                if(strstr( $ph["value"],$PhoneNumber ))
                    $k=$ph['type'];
                    } 
                    }
                        $result=array("name"=>$accounts["name"],"linetype"=>$k,"id"=>$accounts['id']);
                        $Name= $accounts["name"]." (".$k.")";
                        $found=true;
                        break;

                    } 

                }else
                    if((strlen($PhoneNumber)>=strlen($accounts["phones"])) and strlen($PhoneNumber)<14) 
                        if(strstr( $PhoneNumber,$accounts["phones"] )) {
                             foreach($accounts["testphone"] as $ph) {
                        if((strlen($PhoneNumber)<=strlen($ph["value"])) and strlen($PhoneNumber)>7) {
                if(strstr( $ph["value"],$PhoneNumber ))
                    $k=$ph['type'];
                    } 
                    }
                        $result=array("name"=>$accounts["name"],"linetype"=>$k,"id"=>$accounts['id']);
                        
                        $found=true;
                        break;                    
                    } 
                } 
            }
     }
        
       if (!$found) return false;
       else return $result;
    
  }

    
    
    
private function wToGreeklish($theGreekText) {
$transmap = array("\xce\xb1" => "\x61", "\xce\xac" => "\x61", "\xce\x91" => "\x41", "\xce\x86" => "\x41", "\xce\xb2" => "\x62", "\xce\x92" => "\x42", "\xce\xb3" => "\x67", "\xce\x93" => "\x47", "\xce\xb4" => "\x64", "\xce\x94" => "\x44", "\xce\xb5" => "\x65", "\xce\xad" => "\x65", "\xce\x95" => "\x45", "\xce\x88" => "\x45", "\xce\xb6" => "\x7a", "\xce\x96" => "\x5a", "\xce\xb7" => "\x69", "\xce\xae" => "\x69", "\xce\x97" => "\x49", "\xce\x89" => "\x49", "\xce\xb8" => "\x74\x68", "\xce\x98" => "\x54\x68", "\xce\xb9" => "\x69", "\xce\xaf" => "\x69", "\xcf\x8a" => "\x69", "\xce\x90" => "\x69", "\xce\x99" => "\x49", "\xce\x8a" => "\x49", "\xce\xaa" => "\x49", "\xce\xba" => "\x6b", "\xce\x9a" => "\x4b", "\xce\xbb" => "\x6c", "\xce\x9b" => "\x4c", "\xce\xbc" => "\x6d", "\xce\x9c" => "\x4d", "\xce\xbd" => "\x6e", "\xce\x9d" => "\x4e", "\xce\xbe" => "\x6b\x73", "\xce\x9e" => "\x4b\x73", "\xce\xbf" => "\x6f", "\xcf\x8c" => "\x6f", "\xce\x9f" => "\x4f", "\xce\x8c" => "\x4f", "\xcf\x80" => "\x70", "\xce\xa0" => "\x50", "\xcf\x81" => "\x72", "\xce\xa1" => "\x52", "\xcf\x83" => "\x73", "\xcf\x82" => "\x73", "\xce\xa3" => "\x53", "\xcf\x84" => "\x74", "\xce\xa4" => "\x54", "\xcf\x85" => "\x79", "\xcf\x8d" => "\x79", "\xcf\x8b" => "\x79", "\xce\xb0" => "\x79", "\xce\xa5" => "\x59", "\xce\x8e" => "\x59", "\xce\xab" => "\x59", "\xcf\x86" => "\x66", "\xce\xa6" => "\x46", "\xcf\x87" => "\x68", "\xce\xa7" => "\x48", "\xcf\x88" => "\x70\x73", "\xce\xa8" => "\x50\x73", "\xcf\x89" => "\x6f", "\xcf\x8e" => "\x6f", "\xce\xa9" => "\x4f", "\xce\x8f" => "\x4f");
return strtr($theGreekText, $transmap);
}
     
/**
 * Saves a missed call in PODIO CRM if the phone number doesn't exist , and returns a Json encodage of the name if it's found .
 * @param   string $PhoneNumber           Phone Number
 * @param   string $Accounts_app_id       Companies App id
 * @param   string $Accounts_app_token    Companies App access token
 * @param   string $People_app_id         People App id
 * @param   string $People_app_token      People App access token
 * @param   array  $options               Podio Item filter options
 * @return  Callers name if found , false if not
 */
    
public function Call($Save_all_Calls,$Use_NumVerifyAPI,$PhoneNumber ,$A_NameItem_externalID,$A_PhoneItem_externalID,$P_NameItem_externalID,$P_PhoneItem_externalID,$calls_app_id,$calls_app_token,$Accounts_app_id,$Accounts_app_token,$People_app_id,$People_app_token,$options){
        $PhoneNumber = $this->CleanPhoneNumber($PhoneNumber);
    
    $Result=$this->SearchCaller($PhoneNumber,$A_NameItem_externalID,$A_PhoneItem_externalID,$P_NameItem_externalID,$P_PhoneItem_externalID,$Accounts_app_id,$Accounts_app_token,$People_app_id,$People_app_token,$options);
    if($Result) {
        $options=array();
        $calls_items = PodioItem::filter($calls_app_id,$options);
        foreach( $calls_items as $itm){
            if($itm->fields["phone"]->values[0]["value"]==$PhoneNumber)
            
    PodioItem::update( $itm->item_id,array(  'fields' => array('relationship' => $Result["id"] )), $options  );
  
    }
        if($Save_all_Calls){
            
    PodioItem::create( $calls_app_id,  array('fields' => array('relationship' => $Result["id"] ,'phone' => array("type"=>$Result["linetype"],"value"=>$PhoneNumber) ,  'date'=>array("start"=>date("Y-m-d H:i:s")  ) )));
            
        }
        return $this->wToGreeklish($Result["name"])." (".$Result["linetype"].")";
    }
    else {

        if($Use_NumVerifyAPI){
            
            require_once 'numverifyAPI.php';
            $temptabele=VerifyPhone("+".$PhoneNumber);
           ($temptabele["line_type"]=="landline")? $line_type="work" : $line_type="mobile";
             PodioItem::create( $calls_app_id,  array(  'fields' => array('phone' => array("type"=>$line_type,"value"=>$PhoneNumber) ,  'date'=>array("start"=>date("Y-m-d H:i:s")  ) )));
        return "Call Saved !";
            
        }else{
                 PodioItem::create( $calls_app_id,  array(  'fields' => array('phone' => array("type"=>"other","value"=>$PhoneNumber) ,  'date'=>array("start"=>date("Y-m-d H:i:s")  ) ) ));
        return "Call Saved !";
        }
       
    }
}
    
      
/**
 * Cleans the phone nuber format .
 * @param   string $PhoneNumber           Phone Number
 * @return  String  clean phone number  !
 */
    
    
private function CleanPhoneNumber($PhoneNumber){

        

            if ($PhoneNumber[0]=='+' ){
                $data= substr($PhoneNumber, 1, strlen($PhoneNumber));

            }   
            else
            if(substr($PhoneNumber, 0, 2)=="00"){

                    $PhoneNumber=substr($PhoneNumber, 2, strlen($PhoneNumber));
                }   
       return $PhoneNumber;
    
    }
 
}

?>
