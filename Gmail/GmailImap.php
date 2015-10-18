<?php
/**
 * Gmail Imap API
 */
class GmailImap{

    function __construct($username = null, $password = null, $spam = false) {
        $this->hostname = $spam ? '{imap.gmail.com:993/ssl}[Gmail]/Spam' : '{imap.gmail.com:993/imap/ssl}INBOX';
        $this->username = $username;
        $this->password = $password;
        $this->connect();
    }

    function connect(){
        if(!($this->connection = imap_open($this->hostname,$this->username,$this->password)))
            throw new Exception('Unable to connect to Gmail'.imap_last_error());
    }

    private function check_if_connection_active(){
        try {
            if(!imap_ping($this->connection))
                $this->connect();
        } catch (Exception $e) {
                $this->connect();
        }
        
    }

    function search_TO_field($email, $fetch_header = false){
        $this->check_if_connection_active();
        $emails = imap_search($this->connection,'TO "'.$email.'"');
        if($emails)
            rsort($emails);
        if(!$fetch_header || !$emails)
            return $emails;
        $connection = $this->connection;
        $headers = array_map(
            function($email_no) use ($connection){
            return imap_fetchheader($connection, $email_no, 0);
        }, $emails);
        return $headers;
    }
    
    function get_new_emails($max_emails_to_check){
        $this->check_if_connection_active();
        $emails = imap_search($this->connection,'ALL');
        // If got emails sort them by latest first
        if($emails)
            rsort($emails);
        $connection = $this->connection;
        $full_emails = array();
        foreach ($emails as $index => $email) 
            if ($index < $max_emails_to_check){
                $header = imap_fetchheader($connection, $email, 0);
                $body = imap_body($connection, $email);
                $full_emails[]= $header.$body;
                
            }
        return $full_emails;
    }

    function send_email($to, $subject, $message){
        $this->check_if_connection_active();
        imap_mail($to, $subject, $message);
    }

    function __destruct(){
        imap_close($this->connection);
    }
}
