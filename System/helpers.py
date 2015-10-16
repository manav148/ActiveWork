import os
import logging
from configurations import parameters
import netifaces
import subprocess as sp

def run(cmd):
    return sp.check_output(cmd.split(), shell=True).strip()


def change_socket_permissions_for_php(socket_name):
    '''
    >>> change_socket_permissions_for_php("tcp://")
    False
    >>> change_socket_permissions_for_php("ipc://")
    False
    '''
    if socket_name.find("ipc:") != 0:
        return False
    file_path = socket_name.split("://")[1]
    if not os.path.isfile(file_path):
        return False
    os.chmod(file_path, 0777)
    # setting owner to www-data
    os.chown(file_path, 33,33)
    return True



def create_logger(name, level):
    '''
    >>> logger = create_logger("blah","info")
    >>> logger.info("hello world")
    >>> logs = file(parameters.LOG_DIR+"/blah.log").read()
    >>> True if logs.find("hello world") >= 0 else False
    True
    '''
    # Get loggin level
    level = getattr(logging,level.upper())
    filename = parameters.LOG_DIR+"/"+name+".log"
    # logging.basicConfig(format = '%(asctime)s:%(levelname)s:%(message)s', level = level, filename = filename)
    # return logging
    logger = logging.getLogger(name)
    logger.setLevel(level)

    # create a file handler
    handler = logging.FileHandler(filename)
    handler.setLevel(level)

    # create a console handler 
    # create console handler and set level to debug
    ch = logging.StreamHandler()
    ch.setLevel(level)

    # create a logging format
    formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    handler.setFormatter(formatter)
    ch.setFormatter(formatter)

    # add the handlers to the logger
    logger.addHandler(handler)
    logger.addHandler(ch)
    return logger

def get_all_ips():
    '''
    >>> d = get_all_ips()
    >>> type(d)
    <type 'list'>
    '''
    interfaces = netifaces.interfaces()
    ips = []
    for x in interfaces:
         int = netifaces.ifaddresses(x).get(netifaces.AF_INET,None)
         if int: ips.append(int[0]['addr'])
    return ips

def get_local_ip(prefix = '192.168.'):
    '''
    >>> ip = get_local_ip()
    >>> ip.startswith('192.168.')
    True
    '''
    ips = get_all_ips()
    [ip] = [ip for ip in ips if ip.startswith(prefix)]
    return ip
