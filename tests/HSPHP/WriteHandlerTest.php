<?php

namespace HSPHP;
use PHPUnit\Framework\TestCase;

class WriteHandlerTest extends TestCase
{
    public function testInsert()
    {
        $io = new WriteSocket();
        $io->connect();
        $t = new HSWriteTest($io);

        $this->assertEquals(0,count($t->select('=',100500)));	// no data with 100500 key

        $t->insert(array('k'=>100500,'v'=>'test\nvalue'));
        $this->assertEquals(array(array('k' => 100500, 'v' => 'test\nvalue')),$t->select('=',100500));
    }

    /**
     * @depends testInsert
     */
    function testUpdate()
    {
        $io = new WriteSocket();
        $io->connect();
        $t = new HSWriteTest($io);

        $this->assertEquals(1,$t->update('=',array('k'=>100500),array('k'=>100500,'v'=>42)));
        $this->assertEquals(array(array('k' => 100500, 'v' => '42')),$t->select('=',100500));
    }

    /**
     * @depends testUpdate
     */
    function testDelete()
    {
        $io = new WriteSocket();
        $io->connect();
        $t = new HSWriteTest($io);

        $this->assertEquals(1,$t->delete('=',array('k'=>100500)));
    }
}
