<?php

/**
 * Represents a persistent hash stored in redis.
 * <pre>
 * $hash = new ARedisHash("myHash");
 * $hash['a key'] = "some value"; // value is instantly saved to redis
 * $hash['another key'] = "some other value"; // value is instantly saved to redis
 * </pre>
 * @author Charles Pick
 * @package packages.redis
 */
class ARedisHash extends ARedisIterableEntity
{

    /**
     * @param $key
     * @param $value
     * @return bool
     * @throws CException
     */
    public function add($key, $value)
    {
        if ($this->name === null) {
            throw new CException(get_class($this) . " requires a name!");
        }
        if ($this->getConnection()->getClient()->hset($this->name, $key, $value) === false) {
            return false;
        }
        $this->_data = null;
        $this->_count = null;
        return true;
    }

    /**
     * @param $key
     * @return bool
     * @throws CException
     */
    public function remove($key)
    {
        if ($this->name === null) {
            throw new CException(get_class($this) . " requires a name!");
        }
        if (!$this->getConnection()->getClient()->hdel($this->name, $key)) {
            return false;
        }
        $this->_data = null;
        $this->_count = null;
        return true;
    }

    /**
     * Returns an iterator for traversing the items in the hash.
     * This method is required by the interface IteratorAggregate.
     * @return Iterator an iterator for traversing the items in the hash.
     * @throws CException
     */
    public function getIterator()
    {
        return new CMapIterator($this->getData());
    }

    /**
     * @return int
     * @throws CException
     */
    public function getCount()
    {
        if ($this->_count === null) {
            if ($this->name === null) {
                throw new CException(get_class($this) . " requires a name!");
            }
            $this->_count = $this->getConnection()->getClient()->hlen($this->name);
        }
        return $this->_count;
    }

    /**
     * @param bool $forceRefresh
     * @return array
     * @throws CException
     */
    public function getData($forceRefresh = false)
    {
        if ($forceRefresh || $this->_data === null) {
            if ($this->name === null) {
                throw new CException(get_class($this) . " requires a name!");
            }
            $this->_data = $this->getConnection()->getClient()->hgetall($this->name);
        }
        return $this->_data;
    }


    /**
     * @param mixed $key
     * @return bool
     * @throws CException
     */
    public function offsetExists($key)
    {
        return $this->getConnection()->getClient()->hExists($this->name, $key); //douma fix hash bug
    }

    /**
     * @param mixed $key
     * @return string
     * @throws CException
     */
    public function offsetGet($key)
    {
        return $this->getConnection()->getClient()->hGet($this->name, $key);
    }

    /**
     * @param mixed $key
     * @param mixed $item
     * @throws CException
     */
    public function offsetSet($key, $item)
    {
        $this->add($key, $item);
    }

    /**
     * @param mixed $key
     * @return int
     * @throws CException
     */
    public function offsetUnset($key)
    {
        return $this->getConnection()->getClient()->hDel($this->name, $key);
    }

    /**
     * @param $key
     * @param int $byAmount
     * @return int
     * @throws CException
     */
    public function increment($key, $byAmount = 1)
    {
        return $this->getConnection()->getClient()->hIncrBy($this->name, $key, $byAmount);
    }

    /**
     * @param $params :参数数组,array('name' => 'Joe', 'salary' => 2000)
     * @return int
     * @throws CException
     */
    public function hMSet($params)
    {
        return $this->getConnection()->getClient()->hMSet($this->name, $params);
    }

    /**
     * @param $hashKeys:hash中的key数组,如array('field1', 'field2')
     * @return array
     * @throws CException
     */
    public function hMGet($hashKeys){
        return $this->getConnection()->getClient()->hMGet($this->name,$hashKeys);
    }


}
