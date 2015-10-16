from datetime import datetime
from datetime import timedelta
import commands
import json
import urllib
import time
from helpers import common
import sys
from helpers.common_config_reader import ConfigReader

def rabbitmqadmin_base_command(format = None, **rabbit_machine_parameters):
    command = "rabbitmqadmin --host " + rabbit_machine_parameters['ip'] + " -P 15672 -u " + rabbit_machine_parameters['user'] + " -p " + rabbit_machine_parameters['pass'] 
    # If rabbit2 use ssl
    if rabbit_machine_parameters['uses_ssl']:
        command += ' -s '
    if format:
        command += ' -f ' + str(format)
    return command

def kill_queue(queue_name, **rabbit_machine_parameters):
    kill_command = rabbitmqadmin_base_command(**rabbit_machine_parameters)
    kill_command += ' delete queue name='
    kill_command += queue_name
    output = commands.getoutput(kill_command)
    if output == "queue deleted":
        return True
    else:
        return False

def curl_uri_base_command(queue_name, **rabbit_machine_parameters):
    base_str = 'curl -k -H "content-type:application/json" -u ' + \
    rabbit_machine_parameters['user'] +":"+ rabbit_machine_parameters['pass'] +\
    " https://" + rabbit_machine_parameters['ip'] + ":15672" +\
    "/api/queues/%2F/" + urllib.quote(queue_name,'')
    base_str += " 2> /dev/null"
    return base_str

def return_queue_data(queue_name, **rabbit_machine_parameters):
    command = curl_uri_base_command(queue_name,**rabbit_machine_parameters)
    out = commands.getoutput(command)
    try:
        return json.loads(out) 
    except Exception:
        return None

def get_email_queues_data(queue_name, **rabbit_machine_parameters):
    data = return_queue_data(queue_name, **rabbit_machine_parameters)
    if not data: 
        return dict(total_consumers = 0, local_consumers = 0, messages = 0, messages_ready = 0)
    consumer_data = dict(total_consumers = data['consumers'])
    consumer_data['local_consumers'] = 0
    consumer_data['messages'] = data['messages']
    consumer_data['messages_ready'] = data['messages_ready']
    ips = common.get_all_ips()
    if data['consumers']:
        for consumer_detail in data['consumer_details']:
            # If the connecting ip is one of this machine increment local consumer counter
            if set([consumer_detail["channel_details"]['peer_host']]).intersection(set(ips)):
                consumer_data['local_consumers'] += 1
    return consumer_data

def get_email_queues(queue_time_string_format="%Y-%m-%d/%H", **rabbit_machine_parameters):
    list_queue_command = rabbitmqadmin_base_command(format = 'raw_json',**rabbit_machine_parameters)
    list_queue_command += ' list queues name'
    valid_queue_names_list = []
    queue_names_list = json.loads(commands.getoutput(list_queue_command))
    for queue_dict in queue_names_list:
        try:
            name = queue_dict['name']
            time.strptime(name, queue_time_string_format)
            valid_queue_names_list.append(name)
        except Exception:
            continue
    return valid_queue_names_list

def get_queue_data_for_email_queues(queue_time_string_format="%Y-%m-%d/%H", **rabbit_machine_parameters):
    queue_data_dict = {}
    email_queues = get_email_queues(queue_time_string_format=queue_time_string_format, **rabbit_machine_parameters)    
    for name in email_queues:
        queue_data_dict[name] = get_email_queues_data(name, **rabbit_machine_parameters) 
    return queue_data_dict

def check_for_hanging_email_queues(age_of_queue = 5,queue_time_string_format="%Y-%m-%d/%H"):
    rabbit_clusters = ConfigReader.get_cluster_machines("rabbit_clusters")
    queue_misbehaving = []
    for rabbit_machine_parameters in rabbit_clusters:
        queue_names = get_email_queues(**rabbit_machine_parameters)    
        for queue_name in queue_names:
            # Check if queue name is a valid time object
            queue_time_object = None
            try:
                queue_time_object = time.strptime(queue_name,queue_time_string_format)
            except ValueError:
                continue
            present_hour = datetime.now()
            #only considering the hour
            present_hour_datetime = datetime(present_hour.year,present_hour.month,present_hour.day,present_hour.hour)
            # time stamp of queue
            queue_datetime = datetime.fromtimestamp(time.mktime(queue_time_object))
            if present_hour_datetime - queue_datetime >= timedelta(hours=age_of_queue):
                queue_misbehaving.append("Rabbit machine : " + rabbit_machine_parameters["public_ip"] + " : has queue " +\
                 queue_name +" older than " + str(age_of_queue) + " hours")
    
    return json.dumps(queue_misbehaving)

if __name__ == '__main__':
    hours = int(sys.argv[1])
    print check_for_hanging_email_queues(hours)