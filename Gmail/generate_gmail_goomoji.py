#!/usr/bin/python3

# 
# This file gets emoji's that we can insert in Gmail email!
# Requires Python3
# 

import base64

def goomoji_decode(code):
    #Base64 decode.
    binary = base64.b64decode(code)
    #UTF-8 decode.
    decoded = binary.decode('utf8')
    #Get the UTF-8 value.
    value = ord(decoded)
    #Hex encode, trim the 'FE' prefix, and uppercase.
    return format(value, 'x')[2:].upper()

def goomoji_encode(code):
    #Add the 'FE' prefix and decode.
    value = int('FE' + code, 16)
    #Convert to UTF-8 character.
    encoded = chr(value)
    #Encode UTF-8 to binary.
    binary = bytearray(encoded, 'utf8')
    #Base64 encode return end return a UTF-8 string.
    return base64.b64encode(binary).decode('utf-8')

goomoji = [ "B0C", "B06", "330", "338", "32B", "360", "349", "343", "332", "364", "35D", "1A5", "347", "322", "35E", "33E", "33D", "35F", "361", "362", "363", "35C", "323", "324", "331", "33A", "33C", "33F", "341", "342", "344", "369", "329", "333", "327", "32F", "320", "326", "B2B", "1B2", "1E0", "1C4", "1E3", "000", "001", "002", "003", "014", "03D", "04D", "4B0", "4EF", "510", "517", "525", "538", "53B", "7D1", "7E4", "800", "801", "813", "814", "81C", "81F", "4F2", "962", "980", "981", "982", "983", "B04", "B05", "B06", "B09", "B0C", "B0E", "B56", "B60", "B68", "4F4"]
sub_goomoji = ['=?UTF-8?B?' + goomoji_encode(x)+'?=' for x in goomoji]
link_goomoji = ['<img src="https://mail.google.com/mail/u/0/e/{goomogi_code}" goomoji="{goomogi_code}">'.format(goomogi_code = x ) for x in goomoji]

print(list(zip(goomoji, sub_goomoji, link_goomoji)))
