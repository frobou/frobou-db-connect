<?php

namespace Frobou\Pdo\Db;

class FrobouPdoConnectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var FrobouPdoConnection
     */
    private $conn;

    /**
     * @var FrobouPdoConfig
     */
    private $config;

    public function setUp()
    {
        $this->config = new FrobouPdoConfig(json_decode(file_get_contents(__DIR__ . '/database.json')), true);
        $this->conn = new FrobouPdoConnection($this->config);
    }

    public function testConectaComBancoDefault()
    {
        $this->assertEquals('ispti', $this->config->getDefaultDb());
    }

}
