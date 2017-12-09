<?php

namespace HSPHP;

use PHPUnit\Framework\TestCase;

class PipelineTest extends TestCase
{
    protected $db = 'HSPHP_test';

    public function setUp()
    {
        if(file_exists('./my.cfg'))
        {
            $this->db = trim(file_get_contents('./my.cfg'));
        }
        parent::setUp();
    }

    public function testMultipleSelect()
    {
        $io = new ReadSocket();
        $io->connect();

        $pipe = new Pipeline($io);

        $accessor = new ReadHandler($pipe,$this->db,'read1',['key'],'',['float']);

        $accessor->select('=',42);
        $accessor->select('=',12);

        $this->assertEquals([[['float'=>'3.14159']],[['float'=>'12345']]],$pipe->execute());
    }

    public function testBigChain()
    {
        $io = new WriteSocket();
        $io->connect();

        $pipe = new Pipeline($io);

        $accessor = new WriteHandler($pipe,$this->db,'write1',['k'],'',['k','v']);

        $accessor->select('=',12);
        $accessor->insert(['k'=>12,'v'=>'v12']);
        $accessor->select('=',12);
        $accessor->update('=',12,['k'=>12,'v'=>'u12']);
        $accessor->select('=',12);
        $accessor->delete('=',12);
        $accessor->select('=',12);

        $ret = $pipe->execute();
        $this->assertEquals([],$ret[0]);
        $this->assertTrue($ret[1]);
        $this->assertEquals([['k'=>'12','v'=>'v12']],$ret[2]);
        $this->assertEquals(1,$ret[3]);
        $this->assertEquals([['k'=>'12','v'=>'u12']],$ret[4]);
        $this->assertEquals(1,$ret[5]);
        $this->assertEquals([],$ret[6]);
    }
}
