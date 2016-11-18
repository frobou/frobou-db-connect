<?php

namespace Frobou\Pdo\Db;

use Frobou\Pdo\Exceptions\FrobouConfigErrorException;
use Frobou\Pdo\Exceptions\FrobouConnectionException;
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

    public function __construct(FrobouPdoConfig $config, $debug = false, Logger $logger = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        $this->logger = $logger;
        $this->default_db = $this->config->getDefaultDb();
    }

    public function connect($db_name = null)
    {
        if (is_null($db_name)) {
            $db_name = $this->config->getDefaultDb();
        } else {
            $this->verifica_se_banco_existe($db_name);
        }
        $configs = $this->config->getSettings();
        try {
            switch (strtolower($configs->connections->{$db_name}->server_type)) {
                case 'mysql':
                    $this->conn[$db_name] = new \PDO(strtolower("mysql:")
                        . "host={$configs->connections->{$db_name}->server_host};"
                        . "dbname={$configs->connections->{$db_name}->db_name};"
                        . "charset={$configs->connections->{$db_name}->server_charset};"
                        . "port={$configs->connections->{$db_name}->server_port}",
                        $configs->connections->{$db_name}->user_name,
                        $configs->connections->{$db_name}->user_pass);
                    break;
                case 'postgre':
                    break;
                case 'sqlite':
                    $this->conn[$db_name] = new \PDO("sqlite:{$configs->connections->{$db_name}->db_name}.sqlite");
                    break;
                default:
                    break;
            }
            if (isset($configs->connections->{$db_name}->attributes)) {
                foreach ($configs->connections->{$db_name}->attributes as $key => $value) {
                    $this->conn[$db_name]->setAttribute(constant($key), constant($value));
                }
            }
        } catch (\PDOException $e) {
            throw new FrobouConnectionException($e->getMessage());
        }
        return true;
    }

    private function disconnect()
    {
        try {
            $this->conn = null;
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
}