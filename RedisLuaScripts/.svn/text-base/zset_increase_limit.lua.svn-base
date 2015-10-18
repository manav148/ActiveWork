redis.call("zincrby",KEYS[1], ARGV[1],ARGV[2])
local count = redis.call("zcard",KEYS[1])
print(count)
if count > 100 then
        redis.call("zremrangebyrank", KEYS[1], 0, 1)
end
return count