<?php

namespace rapidPHP\modules\cache\classier;

use Exception;
use rapidPHP\modules\common\classier\Build;
use rapidPHP\modules\redis\classier\Redis;

class RedisCache extends CacheInterface
{

    /**
     * @var Redis
     */
    private $redis;

    /**
     * RedisCache constructor.
     * @param mixed ...$options
     * @throws Exception
     */
    public function __construct(...$options)
    {
        $this->redis = isset($options[0]) ? $options[0] : null;

        if (!($this->redis instanceof Redis)) throw new Exception('redis instance error!');
    }

    /**
     * exists
     * @param $name
     * @return bool|int
     */
    public function exists($name)
    {
        return $this->redis->exists($name);
    }

    /**
     * 添加缓存
     * @param string $name 缓存名
     * @param $value -值
     * @param int $time 到期时间 0则没有到期时间
     * @return bool
     * @throws Exception
     */
    public function add(string $name, $value, $time = 0): bool
    {
        $cache = ['data' => $value];

        if (is_int($time) && $time > 0) $cache['time'] = time() + $time;

        return $this->redis->set($name, serialize($cache));
    }

    /**
     * 获取缓存
     * @param string $name
     * @return array|string|int|mixed|null
     */
    public function get(string $name)
    {
        if (!$this->exists($name)) return null;

        $cache = $this->redis->get($name);

        if (empty($cache)) return null;

        $cache = unserialize($cache);

        if (empty($cache)) return null;

        $time = isset($cache['time']) ? $cache['time'] : null;

        $data = Build::getInstance()->getData($cache, 'data');

        if (!is_int($time)) return $data;

        if (time() <= $time) {
            return $data;
        } else {
            $this->remove($name);

            return null;
        }
    }

    /**
     * 移除缓存
     * @param string $name
     * @return bool
     */
    public function remove(string $name): bool
    {
        return $this->redis->del($name);
    }
}