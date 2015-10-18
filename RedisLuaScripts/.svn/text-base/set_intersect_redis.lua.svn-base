redis.call("zinterstore",KEYS[1],2, ARGV[1],ARGV[2])
if ARGV[5] then
    return redis.call("zrevrange",KEYS[1],ARGV[3],ARGV[4],ARGV[5])
else
    return redis.call("zrevrange",KEYS[1],ARGV[3],ARGV[4])
end