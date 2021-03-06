<?php

namespace Frobou\Db;

class FrobouPdoConnectionTest extends \PHPUnit_Framework_TestCase
{

    public function testVerificaSeConstrutorFunciona()
    {
        $config = new FrobouDbConfig(json_decode(file_get_contents(__DIR__ . '/database.json')), true);
        $con = new FrobouDbConnection($config);
        $this->assertInstanceOf('Frobou\Db\FrobouDbConnection', $con);
    }

//    public function testConectarComVariosBancos()
//    {
//        $config = new FrobouPdoConfig(json_decode(file_get_contents(__DIR__ . '/database.json')), true);
//        $con = new FrobouPdoConnection($config);
//        $this->assertTrue($con->connect());
//        $this->assertTrue($con->connect('postfix'));
//        $this->assertTrue($con->connect('radius'));
//    }
//
//    /**
//     * @expectedException Frobou\Pdo\Exceptions\FrobouConfigErrorException
//     * @expectedExceptionMessage Database not found
//     */
//    public function testConectarComBancoInformadoFalhando()
//    {
//        $config = new FrobouPdoConfig(json_decode(file_get_contents(__DIR__ . '/database.json')), true);
//        $con = new FrobouPdoConnection($config);
//        $con->connect('postconserto');
//    }

}
