<?php
/**
 * Make Batch calls to Gmail API's
 */
class GmailBatchCall 
{
    
    // function perform_batch_operation{
    function perform_batch_operation($auth_token, $commands, $BOUNDARY = "gmail_data_boundary"){
        $post_body = "";
        if ( $commands ) 
        foreach ($commands as $command) {
            $post_body .= "--$BOUNDARY\n";
            $post_body .= "Content-Type: application/http\n\n";
            $post_body .= $command."\n\n";
        }
        $post_body .= "--$BOUNDARY--\n";

        $headers = [ 'Content-type: multipart/mixed; boundary='.$BOUNDARY, 'Authorization: OAuth '.$auth_token  ];
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, 'https://www.googleapis.com/batch' );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT , 60 ) ;
        curl_setopt($curl, CURLOPT_TIMEOUT, 60 ) ;
        curl_setopt($curl,CURLOPT_POSTFIELDS , $post_body);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $tmp_response =  curl_exec($curl);
        curl_close($curl);
        return $tmp_response;

    }

    function parse_http_response_to_array($http_response){
        $boundary = strtok($http_response, "\n");
        $boundary_exploded = explode($boundary, $http_response);
        $element_count = count($boundary_exploded);
        $response = [];
        foreach ($boundary_exploded as $index => $part) if(trim($part)){
            if( $json_start = strpos($part, "{") ){
                $json_trimmed = trim(substr($part, $json_start));
                $json = json_decode($json_trimmed);
                if(!$json && (($index + 1) == $element_count)) {
                    // Exploding boundary delimiter 
                    $c = explode("--", $json_trimmed);
                    // Getting json part of exploded string
                    $json = json_decode($c[0]);
                }
                $json =  json_data_to_array($json);
                if($json)
                    $response [] = $json;
            }
        }
        
        return $response;
    }
}