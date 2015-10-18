local unread_msg = redis.call('hget', KEYS[1], ARGV[1])
if  unread_msg then
    redis.call('hincrby', KEYS[1], 'total', tonumber(unread_msg) * -1)
    redis.call('hset', KEYS[1], ARGV[1], 0)
    return unread_msg
else
    return nil
end