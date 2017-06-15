<?php

namespace Frobou\Db\Sgdb;

abstract class SgdbAbstract implements SgdbInterface
{
    protected $transaction;
    /**
     * @var \PDO
     */
    protected $connection;
    /**
     * @var \PDOStatement
     */
    protected $stmt = null;
    /**
     * @var
     */
    protected $logger;
    /**
     * @var bool
     */
    protected $debug;

    public function __construct($connection, $transaction, $logger = null, $debug = false)
    {
        $this->transaction = $transaction;
        $this->connection = $connection;
        $this->logger = $logger;
        $this->debug = $debug;
    }
}
