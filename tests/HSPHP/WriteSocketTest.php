<?php

namespace HSPHP;

use PHPUnit\Framework\TestCase;

class WriteSocketTest extends TestCase
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

    public function testInsert()
    {
        $c = new WriteSocket();
        $c->connect('localhost',9999);
        $id = $c->getIndexId($this->db,'write1','','k,v');
        $c->select($id,'=',array(100500));
        $response = $c->readResponse();
        if($response instanceof ErrorMessage) throw $response;
        $this->assertEquals(0,count($response));	// no data with 100500 key
        $c->insert($id,array(100500,'test\nvalue'));
        $response = $c->readResponse();
        if($response instanceof ErrorMessage) throw $response;
        $this->assertEquals(array(),$response);	//return 1 if OK
    }

    /**
     * @depends testInsert
     */
    function testUpdate()
    {
        $c = new WriteSocket();
        $c->connect('localhost',9999);
        $id = $c->getIndexId($this->db,'write1','','k,v');
        $c->update($id,'=',array(100500),array(100500,42));
        $response = $c->readResponse();
        if($response instanceof HSPHP_ErrorMessage) throw $response;
        $this->assertEquals(array(array(1)),$response);
    }

    /**
     * @depends testUpdate
     */
    function testBatchUpdate()
    {
        $c = new WriteSocket();
        $c->connect('localhost',9999);
        $id = $c->getIndexId($this->db,'write1','','k,v');
        $c->update($id,'=',array(100500),array(100500,42), 2, 0, array(100500, 100500));
        $response = $c->readResponse();
        if($response instanceof HSPHP_ErrorMessage) throw $response;
        $this->assertEquals(array(array(2)),$response);
    }

    /**
     * @depends testBatchUpdate
     */
    function testIncrement()
    {
        $c = new WriteSocket();
        $c->connect('localhost',9999);
        $id = $c->getIndexId($this->db,'write1','','k,v');
        $c->increment($id,'=',array(100500),array(1));
        $response = $c->readResponse();
        if($response instanceof HSPHP_ErrorMessage) throw $response;
        $this->assertEquals(array(array(1)),$response);
    }

    /**
     * @depends testIncrement
     */
    function testBatchIncrement()
    {
        $c = new WriteSocket();
        $c->connect('localhost',9999);
        $id = $c->getIndexId($this->db,'write1','','k,v');
        $c->increment($id,'=',array(100501),array(1), 2, 0, array(100501, 100502));
        $response = $c->readResponse();
        if($response instanceof HSPHP_ErrorMessage) throw $response;
        $this->assertEquals(array(array(2)),$response);
    }

    /**
     * @depends testBatchIncrement
     */
    function testDecrement()
    {
        $c = new WriteSocket();
        $c->connect('localhost',9999);
        $id = $c->getIndexId($this->db,'write1','','k,v');
        $c->decrement($id,'=',array(100503),array(1));
        $response = $c->readResponse();
        if($response instanceof HSPHP_ErrorMessage) throw $response;
        $this->assertEquals(array(array(1)),$response);
    }

    /**
     * @depends testDecrement
     */
    function testBatchDecrement()
    {
        $c = new WriteSocket();
        $c->connect('localhost',9999);
        $id = $c->getIndexId($this->db,'write1','','k,v');
        $c->decrement($id,'=',array(100502),array(1), 2, 0, array(100502, 100501));
        $response = $c->readResponse();
        if($response instanceof HSPHP_ErrorMessage) throw $response;
        $this->assertEquals(array(array(2)),$response);
    }

    /**
     * @depends testBatchDecrement
     */
    function testDelete()
    {
        $c = new WriteSocket();
        $c->connect('localhost',9999);
        $id = $c->getIndexId($this->db,'write1','','k,v');
        $c->delete($id,'=',array(100500));
        $response = $c->readResponse();
        if($response instanceof HSPHP_ErrorMessage) throw $response;
        $this->assertEquals(array(array(1)),$response);	//return 1 if OK
        $c->select($id,'=',array(100500));
        $response = $c->readResponse();
        if($response instanceof HSPHP_ErrorMessage) throw $response;
        $this->assertEquals(0,count($response));	// no data with 100500 key
    }
}
