<?php

namespace Frobou\Pdo\Db;

class FrobouPdoAccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FrobouPdoConnection
     */
    private $con;
    public function setUp()
    {
        $config = new FrobouPdoConfig(json_decode(file_get_contents(__DIR__ . '/database.json')), true);
        $this->con = new FrobouPdoConnection($config);
    }

    public function testBegintransacionSomente()
    {
        $this->assertTrue($this->con->beginTransaction());
        $this->assertTrue($this->con->beginTransaction('radius'));
        $this->assertTrue($this->con->beginTransaction('postfix'));
    }

    public function testBegintransacionComRollback()
    {
        $this->con->beginTransaction();
        $this->con->beginTransaction('radius');
        $this->con->beginTransaction('postfix');
        $this->assertTrue($this->con->rollback());
        $this->assertTrue($this->con->rollback('radius'));
        $this->assertTrue($this->con->rollback('postfix'));
    }

    public function testCommitFalhando()
    {
        $this->assertFalse($this->con->commit());
        $this->assertFalse($this->con->commit('radius'));
        $this->assertFalse($this->con->commit('postfix'));
    }
}
