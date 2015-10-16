#!/usr/bin/env python
import commands
import sys, os
from colorama import init, Fore
import argparse

# getting parameters
parser = argparse.ArgumentParser(description='Run command on stack')
parser.add_argument('-mc','--machine-cluster', help='Cluster to run the command on', required=False)
parser.add_argument('-c','--command', help='Command to run on cluster', required=False)
parser.add_argument("-f","--filter", action='append',dest='filter', help='Filter a single m/c in the Cluster Ex: nickname REDIS_CHAT')
args = vars(parser.parse_args())

# print args
# Getting filters
args['filter'] = args['filter'][0].split(',') if args['filter'] else None
init(autoreset=True)
sys.path.append('../python')
try:
    from helpers.common_config_reader import ConfigReader
except Exception, e:
    ConfigReader.load_from_file('../app/config/common_config.json')

def shellquote(s):
    return "'" + s.replace("'", "'\\''") + "'"

def convert_to_boolean(parameter):
    if parameter in ["true", "True","false", "False"]:
        return True if parameter in ["true", "True"] else  False
    else:
        return parameter

cluster = raw_input(Fore.CYAN + "Enter cluster name [all] : ") or "all" if not args["machine_cluster"] else args["machine_cluster"]
command = raw_input(Fore.CYAN + "Enter command name to execute [login] : ") or False if not args["command"] else args["command"]

if command and command != "login":
    command = "hostname; " + command

if cluster == "all":
    public_ips = ConfigReader.get_all_common_parameters("public_ip")
# returning  m/c's after applying filter
elif args['filter']:
    args['filter'][1] = convert_to_boolean(args['filter'][1])
    public_ips = ConfigReader.get_parameter_for_cluster_after_applying_filters( cluster = args["machine_cluster"], parameter = "public_ip",filter_parameter = args['filter'][0], filter_value = args['filter'][1])
else:
    public_ips = ConfigReader.get_parameter_for_cluster(cluster = cluster, parameter = "public_ip")

public_ips = (ip for ip in public_ips if ip)
public_ips = set(public_ips)
for ip in public_ips:
    if command == "login":
        cmd = 'ssh root@' + ip
        os.system(cmd)
        continue
    cmd = 'ssh root@' + ip + " " + shellquote(command) if command else 'ssh root@' + ip
    print(Fore.RED + cmd)
    if command:
        output = commands.getoutput(cmd)
        print(Fore.YELLOW + output)
