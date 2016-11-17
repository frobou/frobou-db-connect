<?php

namespace Frobou\Pdo\Db;

use Monolog\Logger;

abstract class FrobouPdoAccess
{
    /**
     * @var FrobouPdoConfig
     */
    protected $config;
    /**
     * @var bool
     */
    protected $debug;
    /**
     * @var Logger
     */
    protected $logger;

    private function connect($db_name = null)
    {
        if (is_null($db_name)) {
            $db_name = $this->config->getDefaultDb();
        } else {
            $this->config->verifica_se_banco_existe($db_name);
        }
        $attrs = [];
        if (count($this->config->getAttributes() > 0)) {
            foreach ($this->config->getAttributes() as $attr) {
                $attrs[$attr['param']] = $attr['value'];
            }
        }
        try {
            $this->conn = new PDO("{$this->config->getServertype()}:"
                . "host={$this->config->getServername()};"
                . "dbname={$this->config->getDbname()};"
                . "charset={$this->config->getCharset()};"
                . "port={$this->config->getPort()}", $this->config->getUsername(), $this->config->getPassword(), $attrs);
        } catch (PDOException $e) {
            $this->setError($e->getCode(), 'SGDB connect error');
            return false;
        }
        return true;
    }

    private function disconnect()
    {

    }

    public function startTransaction()
    {

    }

    public function commit()
    {

    }

    public function rollback()
    {

    }

    public function select()
    {
    }

    public function insert()
    {
    }

    public function update()
    {
    }

    public function delete()
    {
    }

    /**
     * estatisticas de operacao, como lastinsertid, rowcount, erros, etc
     */
    public function stats()
    {

    }

    private function execute()
    {

    }

}