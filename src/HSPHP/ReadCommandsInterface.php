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
 * Read commands interface
 *
 * @package HSPHP
 * @author  Nuzhdin Urii <nuzhdin.urii@gmail.com>
 */

interface ReadCommandsInterface
{
    /**
     * Perform opening index $index over $key of table $db.$table and preparing read $fields
     *
     * @param integer $index
     * @param string  $db
     * @param string  $table
     * @param string  $key
     * @param string  $fields
     *
     * @return void
     */
    public function openIndex(int $index, string $db, string $table, string $key, string $fields);

    /**
     * Register index Id in socket and return it,caches indexes for future use
     *
     * @param string $db
     * @param string $table
     * @param string $key
     * @param string $fields
     *
     * @return integer
     */
    public function getIndexId(string $db, string $table, string $key, string $fields);

    /**
     * Perform select command using compare method for keys
     *
     * @see https://github.com/DeNA/HandlerSocket-Plugin-for-MySQL/blob/master/docs-en/protocol.en.txt
     *
     * @param integer $index
     * @param string $compare
     * @param array $keys
     * @param integer $limit
     * @param integer $begin
     * @param array $in
     *
     * @return void
     */
    public function select(int $index, string $compare, array $keys, int $limit = 1, int $begin = 0, array $in = []);
}
