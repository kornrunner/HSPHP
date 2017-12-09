<?php

namespace HSPHP;

use PHPUnit\Framework\TestCase;

class ReadHandlerTest extends TestCase
{
    function testSelect()
    {
        $io = new ReadSocket();
        $io->connect();
        $t = new HSReadTest($io);
        $this->assertEquals([[
            'key'       => 42,
            'date'      => '2010-10-29',
            'float'     => '3.14159',
            'varchar'   => 'variable length',
            'text'      => "some\r\nbig\r\ntext",
            'set'       => 'a,c',
            'union'     => 'b',
            'null'      => NULL]],$t->select('=',42));
    }
}
