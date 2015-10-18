<?php

class GmailHistoryID {

    private function getCurledData($url, $headers){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT , 60 ) ;
        curl_setopt($curl, CURLOPT_TIMEOUT, 60 ) ;
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $curl_response =  curl_exec($curl);
        curl_close($curl);
        return json_decode($curl_response, true);
    }

    function getLastInboxMessage( $email_id , $access_token ) {
        $headers = [ 
                        'Content-type: application/json', 
                        'Authorization: OAuth '.$access_token  
                    ];
        $url = 'https://www.googleapis.com/gmail/v1/users/'.urlencode($email_id).'/messages?labelIds=INBOX&maxResults=1';
        $data = $this->getCurledData($url, $headers);
        return $data ;
    }   

    function getLastSentMessage( $email_id , $access_token ) {
        $headers = [ 
                        'Content-type: application/json', 
                        'Authorization: OAuth '.$access_token  
                    ];
        $url = 'https://www.googleapis.com/gmail/v1/users/'.urlencode($email_id).'/messages?labelIds=SENT&maxResults=1';
    
        $data = $this->getCurledData($url, $headers);
        return $data ;
    }   

    function getLastMessage( $email_id , $access_token ) {
        $headers = [ 
                        'Content-type: application/json', 
                        'Authorization: OAuth '.$access_token  
                    ];
        $url = 'https://www.googleapis.com/gmail/v1/users/'.urlencode($email_id).'/messages?maxResults=1';
    
        $data = $this->getCurledData($url, $headers);
        return $data ;
    }   

    function getLastMessageID( $email_id , $access_token ) {
        // check last message
        $data = $this->getLastMessage( $email_id , $access_token ) ;
        if ( @$data['messages'][0]['id'] ) 
            return @$data['messages'][0]['id'] ; 
    }   

    function getHistoryIDFromMessageID( $email_id , $message_id , $access_token ) {
        $headers = [ 
                        'Content-type: application/json', 
                        'Authorization: OAuth '.$access_token  
                    ];
        $url = 'https://www.googleapis.com/gmail/v1/users/'.urlencode($email_id).'/messages/'.urlencode($message_id).'?format=minimal';
    
        $data = $this->getCurledData($url, $headers);
        return @$data['historyId'];
    }   

}