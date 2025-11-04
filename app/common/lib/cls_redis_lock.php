<?php

namespace app\common\lib;

use think\facade\Cache;

/**
 * 遇锁立刻返回
 * if(cls_redis_lock::lock(‘test’)) {
 *  show_error();
 *  return;
 * }
 * do_job();
 * cls_redis_lock::unlock();
 *
 * 遇锁等待3秒
 * if(cls_redis_lock::lock(‘test’, 3)) {
 *  do_job();
 *  cls_redis_lock::unlock(‘test’);
 * }
 */
class cls_redis_lock
{
    /**
     * 加锁
     * @param $name
     * @param int $timeout 循环获取锁的等待超时时间, 在此时间内会一直赏试获取锁直到超时
     * @param int $expire 当前锁的最大超时时间, 必须大于0, 如果超过生存时间锁仍未被释放, 则系统会自动强制释放
     * 获取锁失败后挂起再试的时间间隔
     * @param int $wait_interval_us 获取锁失败后挂起再试的时间间隔
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function lock($name, $timeout = 0, $expire = 15, $wait_interval_us = 100000)
    {
        if ($name == null) {
            return false;
        }

        //取得当前时间
        $now = time();
        $timeout_at = $now + $timeout;  //获取锁失败时的等待超时时刻
        $expire_at = $now + $expire;    //锁的最大生存时刻
        $redis = Cache::store('redis')->handler();
        $key = "lock:{$name}";

        while (true)
        {
            //将key的最大生存时刻存到redis里，过了这个时刻该锁会被自动释放
            $result = $redis->setnx($key, $expire_at);
            if ($result != false)
            {
                //设置key的失效时间
                $redis->expire($key, $expire);
                return true;
            }

            //以秒为单位, 返回给定key的剩余生存时间
            //ttl小于0表示key上没有设置生存时间 (key是不会不存在的, 因前面setnx会自动创建)
            //如果出现该情况, 那就是进程的某个实例setnx成功后 crash 导致紧跟著的expire没有被调用, 这时可以直接设置expire并把锁纳为己用
            $ttl = $redis->ttl($key);
            if($ttl < 0) {
                $redis->set($key, $expire_at);
                return true;
            }

            //循环请求锁，如果没设置锁失败的等待时间 或者 已超过最大等待时间了，那就退出
            if ($timeout <= 0 || $timeout_at < microtime(true))
            {
                break;
            }

            //隔 $wait_interval_us 0.1秒后继续 请求
            usleep($wait_interval_us);
        }

        return false;
    }

    /**
     * 解锁
     * @param $name
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function unlock($name)
    {
        $redis = Cache::store('redis')->handler();

        //先判断是否存在此锁
        if ( self::is_locking($name) )
        {
            //删除锁
            if($redis->del("lock:{$name}"))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 给当前所增加指定生存时间，必须大于0
     * @param $name
     * @param $expire
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function expire($name, $expire)
    {
        $redis = Cache::store('redis')->handler();

        //先判断是否存在该锁
        if (self::is_locking($name))
        {
            //所指定的生存时间必须大于0
            $expire = max($expire, 1);
            //增加锁生存时间
            if($redis->expire("lock:{$name}", $expire))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断当前是否拥有指定名字的所
     * @param $name
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function is_locking($name)
    {
        $redis = Cache::store('redis')->handler();

        //从redis返回该锁的生存时间
        return $redis->get("lock:{$name}");
    }
}
