<?php

namespace Frobou\Pdo\Db;

use Frobou\Pdo\Db\Sgdb\Mysql;
use Frobou\Pdo\Db\Sgdb\Postgre;
use Frobou\Pdo\Db\Sgdb\Sqlite;
use Frobou\Pdo\Exceptions\FrobouConfigErrorException;
use Frobou\Pdo\Exceptions\FrobouConnectionException;
use Frobou\Pdo\Exceptions\FrobouSgdbErrorException;
use Monolog\Logger;

class FrobouPdoConnection extends FrobouPdoAccess
{
    /**
     * @var FrobouPdoConfig
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

    public function __construct(FrobouPdoConfig $config, $debug = false, Logger $logger = null)
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
            $this->verifica_se_banco_existe($db_name);
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
                        $this->configs->connections->{$db_name}->user_pass);
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
                    is_null(constant($value)) ? $val = $value : $val = constant($value);
                    $this->conn[$db_name]->setAttribute(constant($key), $val);
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
        } catch (PDOException $e) {
            throw new FrobouConnectionException('Error disconecting from sgdb');
        }
    }

    private function verifica_se_banco_existe($db_name)
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