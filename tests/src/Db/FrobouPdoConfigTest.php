<?php

namespace Frobou\Db;

class FrobouPdoConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testConfiguracaoDeBancoDefault()
    {
        $cfg = new FrobouDbConfig(json_decode(file_get_contents(__DIR__ . '/database_uno.json')), true);
        $this->assertEquals('radius', $cfg->getDefaultDb());

        $cfg2 = new FrobouDbConfig(json_decode(file_get_contents(__DIR__ . '/database.json')), true);
        $this->assertEquals('ispti', $cfg2->getDefaultDb());
    }

    /**
     * @expectedException Frobou\Db\Exceptions\FrobouConfigErrorException
     * @expectedExceptionMessage Db config is invalid
     */
    public function testFalhaNaConfiguracao()
    {
        new FrobouDbConnection(new FrobouDbConfig(new \stdClass()));
    }

    /**
     * @expectedException Frobou\Db\Exceptions\FrobouConfigErrorException
     * @expectedExceptionMessage Default db is not informed
     */
    public function testFalhaNaConfiguracaoPorFaltarDefault()
    {
        new FrobouDbConfig(json_decode(file_get_contents(__DIR__ . '/database_error.json')), true);
    }

    /**
     * @expectedException Frobou\Db\Exceptions\FrobouConfigErrorException
     * @expectedExceptionMessage Incorrect data structure: field(s) server_port not found
     */
    public function testFalhaNaConfiguracaoPorFaltarCampos()
    {
        new FrobouDbConfig(json_decode(file_get_contents(__DIR__ . '/database_syntax_error.json')), true);
    }

    /**
     * @expectedException Frobou\Db\Exceptions\FrobouConfigErrorException
     * @expectedExceptionMessage Server type is not allowed
     */
    public function testFalhaNaConfiguracaoPorServerTypeIncorreto()
    {
        new FrobouDbConfig(json_decode(file_get_contents(__DIR__ . '/database_server_type.json')), true);
    }

}
