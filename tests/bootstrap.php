<?php

namespace HSPHP;

$autoload = require __DIR__.'/../vendor/autoload.php';

class HSPipelineTest extends WriteHandler
{
    public function __construct($io)
    {
        $db = 'HSPHP_test';
        if(file_exists(__DIR__.'/HSPHP/my.cfg'))
        {
            $db = trim(file_get_contents(__DIR__.'/HSPHP/my.cfg'));
        }
        parent::__construct($io,$db,'write1',array('k'),'',array('k','v'));
    }
}

class HSReadTest extends ReadHandler
{
    function __construct($io)
    {
        $db = 'HSPHP_test';
        if(file_exists(__DIR__.'/HSPHP/my.cfg'))
        {
            $db = trim(file_get_contents(__DIR__.'/HSPHP/my.cfg'));
        }
        parent::__construct($io,$db,'read1',array('key'),'',array('key','date','float','varchar','text','set','union','null'));
    }
}

class HSWriteTest extends WriteHandler
{
    function __construct($io)
    {
        $db = 'HSPHP_test';
        if(file_exists(__DIR__.'/HSPHP/my.cfg'))
        {
            $db = trim(file_get_contents(__DIR__.'/HSPHP/my.cfg'));
        }
        parent::__construct($io,$db,'write1',array('k'),'',array('k','v'));
    }
}
