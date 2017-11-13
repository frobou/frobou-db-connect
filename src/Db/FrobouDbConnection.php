<?php

namespace Frobou\Db;

use Frobou\Db\Sgdb\Mysql;
use Frobou\Db\Sgdb\Postgre;
use Frobou\Db\Sgdb\Sqlite;
use Frobou\Db\Exceptions\FrobouConfigErrorException;
use Frobou\Db\Exceptions\FrobouConnectionException;
use Frobou\Db\Exceptions\FrobouSgdbErrorException;
use Monolog\Logger;

class FrobouDbConnection extends FrobouDbAccess
{
    /**
     * @var FrobouDbConfig
     */
    protected $config;
    protected $debug;
    /**
     * @var Logger
     */
    protected $logger;
    protected $conn = [];
    protected $transaction = [];
    protected $db = [];

    public function __construct(FrobouDbConfig $config, $debug = false, Logger $logger = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        $this->logger = $logger;
        $this->default_db = $this->config->getDefaultDb();
    }

    protected function connect($db_name = null)
    {
        if (is_null($db_name)) {
            $db_name = $this->config->getDefaultDb();
        } else {
            $this->verificaSeBancoExiste($db_name);
        }
        $this->configs = $this->config->getSettings();
        try {
            switch (strtolower($this->configs->connections->{$db_name}->server_type)) {
                case 'mysql':
                    $charset = 'utf8';
                    if (isset($this->configs->connections->{$db_name}->server_charset)){
                        $charset = $this->configs->connections->{$db_name}->server_charset;
                    }
                    $this->conn[$db_name] = new \PDO(strtolower("mysql:")
                        . "host={$this->configs->connections->{$db_name}->server_host};"
                        . "dbname={$this->configs->connections->{$db_name}->db_name};"
                        . "charset={$charset};"
                        . "port={$this->configs->connections->{$db_name}->server_port}",
                        $this->configs->connections->{$db_name}->user_name,
                        $this->configs->connections->{$db_name}->user_pass,
                        [[\PDO::MYSQL_ATTR_LOCAL_INFILE => TRUE]]);
                    break;
                case 'postgre':
                    break;
                case 'sqlite':
                    $this->conn[$db_name] = new \PDO("sqlite:{$this->configs->connections->{$db_name}->db_name}.sqlite");
                    break;
                default:
                    break;
            }
            if (isset($this->configs->connections->{$db_name}->attributes)) {
                foreach ($this->configs->connections->{$db_name}->attributes as $key => $value) {
                    if (is_null(constant($key))){
                        continue;
                    }
                    try {
                        $val = constant($value);
                    } catch (\Exception $e){
                        $val = $value;
                    }
                    try{
                        $this->conn[$db_name]->setAttribute(constant($key), $val);
                    } catch (\Exception $e){
                        throw new FrobouConnectionException($e->getMessage());
                    }
                }
            }
        } catch (\PDOException $e) {
            throw new FrobouConnectionException($e->getMessage());
        }
        return true;
    }

    protected function disconnect($db_name)
    {
        $db_name = $this->selectDb($db_name);
        try {
            $this->conn[$db_name] = null;
            return true;
        } catch (\PDOException $e) {
            throw new FrobouConnectionException('Error disconecting from sgdb');
        }
    }

    private function verificaSeBancoExiste($db_name)
    {
        if (!in_array($db_name, $this->config->getDbNames())) {
            throw new FrobouConfigErrorException('Database not found');
        }
        return true;
    }

    protected function selectDb($db_name)
    {
        if (is_null($db_name)) {
            return $this->config->getDefaultDb();
        }
        return $db_name;
    }

    protected function dbInstance($db_name)
    {
        $db_name = $this->selectDb($db_name);
        if (!isset($this->conn[$db_name]) || !$this->conn[$db_name]->inTransaction()){
            $this->connect($db_name);
        }
        switch (strtolower($this->configs->connections->{$db_name}->server_type)) {
            case 'mysql':
                if (!isset($this->db[$db_name])){
                    $this->db[$db_name] = new Mysql($this->conn[$db_name], $this->conn[$db_name]->inTransaction(), $this->logger);
                }
                break;
            case 'postgre':
                return new Postgre();
                break;
            case 'sqlite':
                return new Sqlite();
                break;
            default:
                throw new FrobouSgdbErrorException();
                break;
        }
        return $this->db[$db_name];
    }
}
