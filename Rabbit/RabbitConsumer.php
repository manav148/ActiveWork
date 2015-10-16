<?php
/**
 * Usage : 
 * $RabbitConsumer = new RabbitConsumer($queue_name, $rabbit_server_id = false, $prefetch_size =0 ,$prefetch_count = 10, 
 * $script_kill = 290, $max_consumers = 10, $kill_if_not_lead_webserver = true, $kill_if_no_messages = True, $wait_timeout = 5);
 * $callback = function( $msg ) {} ;
 * $RabbitConsumer->register_callback_function( $callback ) ;
 */
use PhpAmqpLib\Connection\AMQPConnection;
class RabbitConsumer
{
    
    function __construct($queue, $rabbit_server_id = false, $prefetch_size =0 ,$prefetch_count = 10, $script_kill = 290, $max_consumers = 10, $kill_if_not_lead_webserver = True, $kill_if_no_messages = True, $wait_timeout = null )
    {
        $this->queue = $queue;
        $this->rabbit_server_id = $rabbit_server_id;
        $this->prefetch_size = $prefetch_size;
        $this->prefetch_count = $prefetch_count;
        $this->script_kill = $script_kill;
        $this->max_consumers = $max_consumers;
        $hostname = gethostname();
        $this->consumer_tag = "$hostname:consumer:". getmypid ();
        $this->kill_if_not_lead_webserver = $kill_if_not_lead_webserver;
        $this->kill_if_no_messages = $kill_if_no_messages;
        $this->wait_timeout = $wait_timeout;
    }


    function register_callback_function( $callback ) {
        // Check if this script should be run on this webserver
        global $WebServer;
        if( $this->kill_if_not_lead_webserver && !$WebServer->isLeadWebserver() )
            die( "This server is not lead webserver" );
        if($this->script_kill){
            global $end_time;
            $end_time = ( time() + ( $this->script_kill )); //kill the script in 290 seconds
        }
        $RabbitServer = new RabbitServer();
        if(!$this->rabbit_server_id)
            $RabbitServer->loadRandomServerForProcessing();
        else
            $RabbitServer->loadByID($this->rabbit_server_id);
        $this->conn = new AMQPConnection($RabbitServer->getIP(), $RabbitServer->getPort(),
        $RabbitServer->getUser(), $RabbitServer->getPassword(), $RabbitServer->getVhost());
        $this->channel = $this->conn->channel();
        $this->channel->queue_declare($this->queue, false, true, false, false);

        $RabbitQueueMetadata = new RabbitQueueMetadata($RabbitServer);
        $queue_data = $RabbitQueueMetadata->get_queue_data($this->queue);

        if($this->max_consumers && $queue_data["consumers"] > $this->max_consumers){
            die("Too many consumers #: ".$this->max_consumers );
        }
        
        if($this->kill_if_no_messages && $queue_data['messages_ready'] == 0){
            $message_tmp = "\nNo messages" ;
            sleep ( 1 ) ;
            echo $message_tmp ;
            return ;
        }
        
        $this->channel->basic_qos($this->prefetch_size, $this->prefetch_count, false);
        $this->channel->basic_consume($this->queue, $this->consumer_tag, $no_local=false,
                                      $no_ack=false, $exclusive=false, $nowait=false, $callback);

        register_shutdown_function(array($this, 'shutdown'), $this->channel, $this->conn);
        // Loop as long as the channel has callbacks registered
        while ( count($this->channel->callbacks)) {
            try {
                $this->channel->wait( null , null , $this->wait_timeout ) ;
            }
            catch (Exception $e) {
                echo "\nWe waited but didn't get a message" ;
                return ;
            }
        }
    }

    function shutdown(){
            $this->channel->close();
            $this->conn->close();
    }

}