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
 * Class for work with Socket
 *
 * @package HSPHP
 * @author  Nuzhdin Urii <nuzhdin.urii@gmail.com>
 */

class ReadSocket implements ReadCommandsInterface
{
    const EOL       = "\n";
    const SEP       = "\t";
    const NULL      = "\0";
    const ESC       = "\1";
    const ESC_SHIFT = 0x40;

    private static $decodeMap = [
        "\x01\x40" => "\x00",
        "\x01\x41" => "\x01",
        "\x01\x42" => "\x02",
        "\x01\x43" => "\x03",
        "\x01\x44" => "\x04",
        "\x01\x45" => "\x05",
        "\x01\x46" => "\x06",
        "\x01\x47" => "\x07",
        "\x01\x48" => "\x08",
        "\x01\x49" => "\x09",
        "\x01\x4A" => "\x0A",
        "\x01\x4B" => "\x0B",
        "\x01\x4C" => "\x0C",
        "\x01\x4D" => "\x0D",
        "\x01\x4E" => "\x0E",
        "\x01\x4F" => "\x0F"
    ];

    private static $encodeMap = [
        "\x00" => "\x01\x40",
        "\x01" => "\x01\x41",
        "\x02" => "\x01\x42",
        "\x03" => "\x01\x43",
        "\x04" => "\x01\x44",
        "\x05" => "\x01\x45",
        "\x06" => "\x01\x46",
        "\x07" => "\x01\x47",
        "\x08" => "\x01\x48",
        "\x09" => "\x01\x49",
        "\x0A" => "\x01\x4A",
        "\x0B" => "\x01\x4B",
        "\x0C" => "\x01\x4C",
        "\x0D" => "\x01\x4D",
        "\x0E" => "\x01\x4E",
        "\x0F" => "\x01\x4F"
    ];

    protected $socket = NULL;

    /** @var array */
    protected $indexes = [];

    /** @var integer */
    protected $currentIndex = 1;

    /**
     * Connect to Handler Socket
     *
     * @param string  $server
     * @param integer $port
     *
     * @throws IOException
     */
    public function connect(string $server = 'localhost', int $port = 9998, float $connect_timeout = 1, float $timeout = 1)
    {
        $addr = "tcp://{$server}:{$port}";
        $this->socket = @stream_socket_client($addr, $errc, $errs, $connect_timeout, STREAM_CLIENT_CONNECT);
        if (!$this->socket) {
            throw new IOException("Connection to {$server}:{$port} failed");
        }
        $timeout_pos = abs($timeout);
        $timeout_sec = (int) $timeout_pos;
        stream_set_timeout($this->socket, $timeout_sec, ($timeout_pos - $timeout_sec) * 1000000);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Disconnect from server
     */
    public function disconnect()
    {
        if ($this->socket !== NULL) {
            @fclose($this->socket);
        }

        $this->socket = NULL;
        $this->indexes = [];
        $this->currentIndex = 1;
    }

    /**
     * Is connected
     *
     * @return bool
     */
    public function isConnected()
    {
        return is_resource($this->socket);
    }

    /**
     * Receive one string from server,string haven't trailing \n
     *
     * @param Boolean $read Specifies socket
     *
     * @throws IOException
     *
     * @return string
     */
    protected function recvStr(bool $read = true)
    {
        $str = @fgets($this->socket);
        if (!$str) {
            $this->disconnect();
            throw new IOException('Cannot read from socket');
        }
        return substr($str, 0, -1);
    }

    /**
     * Send command to server
     *
     * @param string $string
     *
     * @throws IOException
     */
    protected function sendStr(string $string)
    {
        if (!$this->isConnected()) {
            throw new IOException('No active connection');
        }

        $string = (string)$string;
        while ($string) {
            $bytes = @fwrite($this->socket, $string);
            if ($bytes === false) {
                $this->disconnect();
                throw new IOException('Cannot write to socket');
            }

            if ($bytes === 0) {
                return;
            }
            $string = substr($string, $bytes);
        }
    }

    /**
     * Encode string for sending to server
     *
     * @param string $string
     *
     * @return string
     */
    protected function encodeString(string $string)
    {
        if (is_null($string)) {
            return self::NULL;
        } else {
            return strtr($string, self::$encodeMap);
        }
    }

    /**
     * Decode string from server
     *
     * @param string $encoded
     * @return string
     */
    protected function decodeString($encoded)
    {
        if ($encoded === self::NULL) {
            return NULL;
        } else {
            return strtr($encoded, self::$decodeMap);
        }
    }

    /**
     * Read response from server
     *
     * @return ErrorMessage | array
     */
    public function readResponse()
    {
        $response = $this->recvStr();
        $vals = explode(self::SEP, $response);
        if ($vals[0] != 0) {
            //error occured
            return new ErrorMessage(isset($vals[2]) ? $vals[2] : '', $vals[0]);
        } else {
            array_shift($vals); // skip error code
            $numCols = intval(array_shift($vals));
            $vals = array_map([$this, 'decodeString'], $vals);
            $result = array_chunk($vals, $numCols);

            return $result;
        }
    }

    /**
     * Authenticate a connection
     *
     * @param string $authkey
     *
     * @return ErrorMessage | boolean
     */
    public function authenticate(string $authkey)
    {
	$this->sendStr(implode(self::SEP, ['A',
		$this->encodeString($authkey)
	   ]) . self::EOL
	);
	$ret = $this->readResponse();
	if (! $ret instanceof ErrorMessage) {
	    return TRUE;
	} else {
	    throw $ret;
	}
    }

    /**
     * {@inheritdoc}
     */
    public function openIndex(int $index, string $db, string $table, string $key, string $fields)
    {
        if (empty($key)) {
            $key = 'PRIMARY';
        }

        $this->sendStr(implode(self::SEP, ['P',
                intval($index),
                $this->encodeString($db),
                $this->encodeString($table),
                $this->encodeString($key),
                $this->encodeString($fields)
            ]) . self::EOL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexId($db, string $table, string $key, $fields)
    {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        if (isset($this->indexes[$db][$table][$key][$fields])) {
            return $this->indexes[$db][$table][$key][$fields];
        } else {
            //register new index ,save it and return
            $this->openIndex($this->currentIndex, $db, $table, $key, $fields);
            $ret = $this->readResponse();
            if (!$ret instanceof ErrorMessage) {
                $this->indexes[$db][$table][$key][$fields] = $this->currentIndex++;
                return $this->currentIndex - 1;
            } else {
                throw $ret;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function select(int $index, string $compare, $keys, int $limit = 1, int $begin = 0, array $in = [])
    {
        $ivlen = count($in);

        $query = $index . self::SEP . $compare . self::SEP . count($keys);

        foreach ($keys as $key) {
            $query .= self::SEP . $this->encodeString((string)$key);
        }

        if ($begin > 0 || $ivlen > 0) {
            $query .= self::SEP . (($ivlen > 0) ? $ivlen : $limit) . self::SEP . $begin;
        } else {
            if ($limit > 1) {
                $query .= self::SEP . $limit;
            }
        }

        if ($ivlen) {
            $query .= self::SEP . '@' . self::SEP . '0' . self::SEP . $ivlen;

            foreach($in as $value) {
                $query .= self::SEP . $this->encodeString((string)$value);
            }
        }

        $this->sendStr($query . self::EOL);
    }

    /**
     * Register callback that must process response from server
     * very useful for cache/pipeline system
     *
     * @param callback $callback
     * @return mixed
     */
    public function registerCallback($callback)
    {
        return call_user_func($callback, $this->readResponse());
    }

}
