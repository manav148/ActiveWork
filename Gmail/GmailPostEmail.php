<?php
class GmailPostEmail
{
    
    function __construct($base64email, $sender_email_id, $access_token){
        $this->email = $base64email;
        $this->email_id = $sender_email_id;
        $this->access_token = $access_token;
        $this->curl_response = $this->curl_gmail_api();
    }

    function get_curl_response(){
        return $this->curl_response;
    }

    function curl_gmail_api(){
        $payload = json_encode(["raw" => $this->email]);
        $headers = [ 'Content-type: application/json', 
                    'Content-Length: '.strlen($payload), 
                    'Authorization: OAuth '.$this->access_token  
                    ];

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, 'https://www.googleapis.com/gmail/v1/users/'.urlencode($this->email_id).'/messages/send' );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT , 60 ) ;
        curl_setopt($curl, CURLOPT_TIMEOUT, 60 ) ;
        curl_setopt($curl,CURLOPT_POSTFIELDS , $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $curl_response =  curl_exec($curl);
        curl_close($curl);
        return $curl_response;
    }
}