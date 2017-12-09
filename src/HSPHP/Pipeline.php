<?php

/*
 * This file is part of HSPHP.
 *
 * (c) Nuzhdin Urii
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HSPHP;

/**
 * Class for execute commands
 *
 * @package HSPHP
 * @author  Nuzhdin Urii <nuzhdin.urii@gmail.com>
 */

class Pipeline implements ReadCommandsInterface, WriteCommandsInterface
{
    /**
     * @var array
     */
    protected $queue = [];

    /**
     * @var ReadSocket | WriteSocket
     */
    protected $socket = NULL;

    public function __construct($socket)
    {
        $this->socket = $socket;
        $this->reset();
    }

    public function reset()
    {
        $this->queue = [];
        $this->accumulate = true;
    }

    public function execute()
    {
        foreach ($this->queue as $call) {
            call_user_func_array([$this->socket, $call['method']], $call['args']);
        }
        $ret = [];
        foreach ($this->queue as $call) {
            $ret[] = call_user_func($call['callback'], $this->socket->readResponse());
        }
        return $ret;
    }

    protected function nullCallback($ret)
    {
        return $ret;
    }

    public function registerCallback($callback = NULL)
    {
        if ($callback === NULL) {
            $callback = $this->nullCallback;
        }

        $this->queue[count($this->queue) - 1]['callback'] = $callback;
        return NULL;
    }

    protected function addToQueue($item)
    {
        $this->queue[] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function select(int $index, string $compare, $keys, int $limit = 1, int $begin = 0, array $in = [])
    {
        $this->addToQueue(['method' => 'select', 'args' => func_get_args()]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $index, string $compare, $keys, $values, int $limit = 1, int $begin = 0, array $in = [])
    {
        $this->addToQueue(['method' => 'update', 'args' => func_get_args()]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $index, string $compare, $keys, int $limit = 1, int $begin = 0)
    {
        $this->addToQueue(['method' => 'delete', 'args' => func_get_args()]);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(int $index, array $values)
    {
        $this->addToQueue(['method' => 'insert', 'args' => func_get_args()]);
    }

    /**
     * {@inheritdoc}
     */
    public function increment(int $index, string $compare, $keys, array $values, int $limit = 1, int $begin = 0, array $in = [])
    {
        $this->addToQueue(['method' => 'increment', 'args' => func_get_args()]);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement(int $index, string $compare, $keys, array $values, int $limit = 1, int $begin = 0, array $in = [])
    {
        $this->addToQueue(['method' => 'decrement', 'args' => func_get_args()]);
    }

    /**
     * {@inheritdoc}
     */
    public function openIndex(int $index, string $db, string $table, string $key, string $fields)
    {
        $this->addToQueue(['method' => 'openIndex', 'args' => func_get_args()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexId(string $db, string $table, string $key, string $fields)
    {
        return $this->socket->getIndexId($db, $table, $key, $fields);
    }
}
