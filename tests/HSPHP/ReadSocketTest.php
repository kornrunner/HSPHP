<?php

namespace HSPHP;

use PHPUnit\Framework\TestCase;

class ReadSocketTest extends TestCase
{
    protected $db = 'HSPHP_test';

    public function setUp()
    {
        if(file_exists(__DIR__.'/my.cfg'))
        {
            $this->db = trim(file_get_contents(__DIR__.'/my.cfg'));
        }
        parent::setUp();
    }

    public function testConnection()
    {
        $c = new ReadSocket();
        $c->connect();
        $this->assertEquals(true,$c->isConnected());
        $c->disconnect();
        $this->assertEquals(false,$c->isConnected());
    }

    public function testConnectionTimeout()
    {
        $this->expectException('HSPHP\IOException');
        $c = new ReadSocket();
        $c->connect('localhost', 9998, 0.0000000001);
        $c->connect('localhost', 19998, 0.0000000001);
    }

    public function testIndex()
    {
        $c = new ReadSocket();
        $c->connect();
        $this->assertEquals(1,$c->getIndexId($this->db,'read1','','key,date,float,varchar,text,set,union,null'));
        $this->assertEquals(1,$c->getIndexId($this->db,'read1','','key,date,float,varchar,text,set,union,null'));
    }

    public function testSelect()
    {
        $c = new ReadSocket();
        $c->connect();
        $id = $c->getIndexId($this->db,'read1','','key,date,float,varchar,text,set,union,null');
        $c->select($id,'=',[42]);
        $response = $c->readResponse();
        $this->assertEquals([[42,'2010-10-29','3.14159','variable length',"some\r\nbig\r\ntext",'a,c','b',NULL]],$response);
    }

    public function testSelectTimeout()
    {
        $this->expectException('HSPHP\IOException');
        $c = new ReadSocket();
        $c->connect('localhost', 9998, 0, 0.00001);
        $id = $c->getIndexId($this->db,'read1','','key,date,float,varchar,text,set,union,null');
        $c->select($id,'=',[42]);
    }

    public function testSelectIn()
    {
        $c = new ReadSocket();
        $c->connect();
        $id = $c->getIndexId($this->db,'read1','','key');
        $c->select($id,'=',[0],0,0,[1,2,3,4,5]);//5 will not be found
        $response = $c->readResponse();
        $this->assertEquals([[1],[2],[3],[4]],$response);
    }

    public function testSelectRange()
    {
        $c = new ReadSocket();
        $c->connect();
        $id = $c->getIndexId($this->db,'read1','','key');
        $c->select($id,'<=',[4],3);
        $response = $c->readResponse();

        $this->assertEquals([[4],[3],[2]],$response);
    }

    public function testSelectMoved()
    {
        $c = new ReadSocket();
        $c->connect();
        $id = $c->getIndexId($this->db,'read1','','key');
        $c->select($id,'<=',[4],1,3);
        $response = $c->readResponse();

        $this->assertEquals([[1]],$response);
    }

    public function testSelectMovedRange()
    {
        $c = new ReadSocket();
        $c->connect();
        $id = $c->getIndexId($this->db,'read1','','key');
        $c->select($id,'<=',[4],2,1);
        $response = $c->readResponse();
        $this->assertEquals([[3],[2]],$response);
    }

    /**
     * @bug 1
     */
    public function testSelectWithZeroValue()
    {
        $c = new ReadSocket();
        $c->connect();
        $id = $c->getIndexId($this->db,'read1','','float');
        $c->select($id,'=',[100]);
        $response = $c->readResponse();
        $this->assertEquals([[0]],$response);
    }

    public function testSelectWithSpecialChars()
    {
        $c = new ReadSocket();
        $c->connect();
        $id = $c->getIndexId($this->db, 'read1', '', 'text');
        $c->select($id, '=', [10001]);
        $response = $c->readResponse();
        $this->assertEquals([["\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F"]], $response);
    }
}
