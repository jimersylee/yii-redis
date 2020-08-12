<?php

/**
 * A cache component that allows items to be cached using redis.
 * @author Charles Pick
 * @package packages.redis
 */
class ARedisCache extends CCache
{
    /**
     * Holds the redis connection
     * @var ARedisConnection
     */
    protected $_connection;


    /**
     * Sets the redis connection to use for caching
     * @param ARedisConnection|string $connection the redis connection, if a string is provided, it is presumed to be a the name of an application component
     */
    public function setConnection($connection)
    {
        if (is_string($connection)) {
            $connection = Yii::app()->{$connection};
        }
        $this->_connection = $connection;
    }

    /**
     * Gets the redis connection to use for caching
     * @return ARedisConnection
     * @throws CException
     */
    public function getConnection()
    {
        if ($this->_connection === null) {
            if (!isset(Yii::app()->redis)) {
                throw new CException("ARedisCache expects a 'redis' application component");
            }
            $this->_connection = Yii::app()->redis;
        }
        return $this->_connection;
    }

    /**
     * Retrieves a value from cache with a specified key.
     * This is the implementation of the method declared in the parent class.
     * @param string $key a unique key identifying the cached value
     * @return string the value stored in cache, false if the value is not in the cache or expired.
     * @throws CException
     */
    protected function getValue($key)
    {
        return $this->getConnection()->getClient()->get($key);
    }

    /**
     * Retrieves multiple values from cache with the specified keys.
     * @param array $keys a list of keys identifying the cached values
     * @return array a list of cached values indexed by the keys
     * @throws CException
     */
    protected function getValues($keys)
    {
        return $this->getConnection()->getClient()->mget($keys);
    }

    /**
     * Stores a value identified by a key in cache.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     * @throws CException
     */
    protected function setValue($key, $value, $expire = 0)
    {

        $this->getConnection()->getClient()->set($key, $value);
        if ($expire) {
            return $this->getConnection()->getClient()->expire($key, $expire);
        }

        return true;

    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     * @throws CException
     */
    protected function addValue($key, $value, $expire)
    {
        if ($expire > 0)
            $expire += time();
        else
            $expire = 0;

        if (!$this->getConnection()->getClient()->setnx($key, $value)) {
            return false;
        }
        if ($expire) {
            $this->getConnection()->getClient()->expire($key, $expire);
        }
        return true;
    }

    /**
     * Deletes a value with the specified key from cache
     * This is the implementation of the method declared in the parent class.
     * @param string $key key of the value to be deleted
     * @return boolean if no error happens during deletion
     * @throws CException
     */
    protected function deleteValue($key)
    {
        return $this->getConnection()->getClient()->del($key);
    }

    /**
     * Deletes all values from cache.
     * Be careful of performing this operation if the cache is shared by multiple applications.
     * @return boolean whether flushing was successful or not
     * @throws CException
     */
    public function flush()
    {
        return (bool)$this->getConnection()->getClient()->flushDb();
    }

    /**
     * @param $key
     * @throws CException
     */
    public function getCache($key)
    {
        $this->getConnection()->getClient()->get($key);
    }

    /**
     * @param $key
     * @param $var
     * @throws CException
     */
    public function setCache($key, $var)
    {
        $this->getConnection()->getClient()->set($key, $var);
    }
}
