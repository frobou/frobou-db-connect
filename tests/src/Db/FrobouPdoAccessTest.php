<?php

namespace Frobou\Db;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class FrobouPdoAccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FrobouDbConnection
     */
    private $con;

    public function setUp()
    {
        $config = new FrobouDbConfig(json_decode(file_get_contents(__DIR__ . '/database.json')), true);
        $log = new Logger('portal');
        $log->pushHandler(new StreamHandler('log-portal.log', Logger::INFO));
        $this->con = new FrobouDbConnection($config, true, $log);
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

        $query = 'INSERT INTO dns_server_data (local_ip,local_port,remote_ip,description) VALUES (\'10.0.0.1\',80,\'10.0.0.1\',\'descricao\')';
        $this->con->insert($query);

        $this->assertTrue($this->con->rollback());
        $this->assertTrue($this->con->rollback('radius'));
        $this->assertTrue($this->con->rollback('postfix'));
    }

    public function testBegintransacionComCommit()
    {
        $this->con->beginTransaction('postfix');
        $this->con->beginTransaction();

        $ip = rand(1, 255);
        $ip2 = rand(1, 255);
        $query = "INSERT INTO dns_server_data (local_ip,local_port,remote_ip,description) VALUES ('32.0.0.{$ip}',80,'32.{$ip}.0.{$ip2}','descricao')";
        $this->con->insert($query);

        $mail = rand(1,10000);
        $query2 = "insert into admin values ('suporte{$mail}@email.com', '123456', '1', '2016-10-10 00:00:00', '2016-10-10 00:00:00', '1')";
        $this->con->insert($query2, 'postfix');
        $this->assertTrue($this->con->commit('postfix'));

        $this->assertTrue($this->con->commit());
    }

    public function testRollbackFalhando()
    {
        $this->assertFalse($this->con->rollback());
        $this->assertFalse($this->con->rollback('radius'));
        $this->assertFalse($this->con->rollback('postfix'));
    }

    public function testCommitFalhando()
    {
        $this->assertFalse($this->con->commit());
        $this->assertFalse($this->con->commit('radius'));
        $this->assertFalse($this->con->commit('postfix'));
    }

    public function testSelect()
    {
        $query = 'SELECT * FROM admin limit 1';
        $this->assertCount(1, $this->con->select($query, 'postfix'));

        $query = 'SELECT * FROM dns_server limit 1';
        $this->assertCount(1, $this->con->select($query));
    }

    /**
     * @expectedException Frobou\Db\Exceptions\FrobouSgdbErrorException
     */
    public function testSelectFalhando()
    {
        $query = 'SELECT * FROM admins limit 1';
        $this->con->select($query, 'postfix');
    }

    public function testSelectRetornandoCount()
    {
        $query = 'SELECT * FROM admin limit 1';
        $this->assertCount(2, $this->con->select($query, 'postfix', [], true));

        $query = 'SELECT * FROM dns_server limit 1';
        $this->assertCount(2, $this->con->select($query, null, [], true));
    }

    public function testInsert()
    {
        $ip = rand(1, 255);
        $ip2 = rand(1, 255);
        $query = "INSERT INTO dns_server_data (local_ip,local_port,remote_ip,description) VALUES ('10.0.0.{$ip}',80,'10.{$ip}.0.{$ip2}','descricao')";
        $this->assertTrue($this->con->insert($query));

        $mail = rand(1,10000);
        $query2 = "insert into admin values ('suporte{$mail}@email.com', '123456', '1', '2016-10-10 00:00:00', '2016-10-10 00:00:00', '1')";
        $this->assertTrue($this->con->insert($query2, 'postfix'));
    }

    /**
     * @expectedException Frobou\Db\Exceptions\FrobouSgdbErrorException
     */
    public function testInsertFalhando()
    {
        $query = "INSERT INTO dns_server_data (local_ip,local_port,remote_ip,description) VALUES ('100.0.0.1',800,'125.121.111.111','descricao')";
        $this->con->insert($query);
    }

    public function testUpdate()
    {
        $query2 = "update dns_server_data set local_ip = '127.0.0.2' where local_ip = '127.0.0.1' and local_port = '8082'";
        $this->assertTrue($this->con->update($query2));

        $query2 = "update dns_server_data set local_ip = '127.0.0.1' where local_ip = '127.0.0.2' and local_port = '8082'";
        $this->assertTrue($this->con->update($query2));

        $query = "UPDATE admin set username = 'suporte@email.com' where username = 'suporte2@email.com'";
        $this->assertTrue($this->con->update($query, 'postfix'));

        $query = "UPDATE admin set username = 'suporte2@email.com' where username = 'suporte@email.com'";
        $this->assertTrue($this->con->update($query, 'postfix'));
    }

    /**
     * @expectedException Frobou\Db\Exceptions\FrobouSgdbErrorException
     */
    public function testUpdateFalhando()
    {
        $query2 = "update dns_server_datas set local_ip = '127.0.0.1' where local_ip = '127.0.0.1' and local_port = '8082'";
        $this->con->delete($query2);
    }

    public function testUpdateNada()
    {
        $query2 = "update dns_server_data set local_ip = '127.0.0.1' where local_ip = '1272.0.0.1' and local_port = '8082'";
        $this->assertFalse($this->con->update($query2));
    }

    public function testDelete(){
        $query = "INSERT INTO dns_server_data (local_ip,local_port,remote_ip,description) VALUES ('333.333.333.333',80,'333.333.333.333','descricao')";
        $this->assertTrue($this->con->insert($query));

        $query2 = "insert into admin values ('email@legal.com.tente', '123456', '1', '2016-10-10 00:00:00', '2016-10-10 00:00:00', '1')";
        $this->assertTrue($this->con->insert($query2, 'postfix'));

        $this->assertTrue($this->con->delete('delete from dns_server_data where local_ip = "333.333.333.333"'));
        $this->assertTrue($this->con->delete('delete from admin where username = "email@legal.com.tente"', 'postfix'));
    }

    /**
     * @expectedException Frobou\Db\Exceptions\FrobouSgdbErrorException
     */
    public function testDeleteFalhando()
    {
        $query2 = "delete from dns_server_datas where local_ip = '127.0.0.1' and local_port = '8082'";
        $this->con->delete($query2);
    }

    public function testDeleteNada()
    {
        $query2 = "delete from dns_server_data where local_ip = '182.55.62.02' and local_port = '1589'";
        $this->assertFalse($this->con->delete($query2));
    }

}
