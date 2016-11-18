<?php

namespace Frobou\Pdo\Db;

class FrobouPdoConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testConfiguracaoDeBancoDefault()
    {
        $cfg = new FrobouPdoConfig(json_decode(file_get_contents(__DIR__ . '/database_uno.json')), true);
        $this->assertEquals('radius', $cfg->getDefaultDb());

        $cfg2 = new FrobouPdoConfig(json_decode(file_get_contents(__DIR__ . '/database.json')), true);
        $this->assertEquals('ispti', $cfg2->getDefaultDb());
    }

    /**
     * @expectedException Frobou\Pdo\Exceptions\FrobouConfigErrorException
     * @expectedExceptionMessage Db config is invalid
     */
    public function testFalhaNaConfiguracao()
    {
        new FrobouPdoConnection(new FrobouPdoConfig(new \stdClass()));
    }

    /**
     * @expectedException Frobou\Pdo\Exceptions\FrobouConfigErrorException
     * @expectedExceptionMessage Default db is not informed
     */
    public function testFalhaNaConfiguracaoPorFaltarDefault()
    {
        new FrobouPdoConfig(json_decode(file_get_contents(__DIR__ . '/database_error.json')), true);
    }

    /**
     * @expectedException Frobou\Pdo\Exceptions\FrobouConfigErrorException
     * @expectedExceptionMessage Config syntax error
     */
    public function testFalhaNaConfiguracaoPorFaltarCampos()
    {
        new FrobouPdoConfig(json_decode(file_get_contents(__DIR__ . '/database_syntax_error.json')), true);
    }

    /**
     * @expectedException Frobou\Pdo\Exceptions\FrobouConfigErrorException
     * @expectedExceptionMessage Server type is not allowed
     */
    public function testFalhaNaConfiguracaoPorServerTypeIncorreto()
    {
        new FrobouPdoConfig(json_decode(file_get_contents(__DIR__ . '/database_server_type.json')), true);
    }

}
