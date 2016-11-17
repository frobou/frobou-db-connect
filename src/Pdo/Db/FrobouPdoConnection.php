<?php

namespace Frobou\Pdo\Db;

use Frobou\Pdo\Exceptions\FrobouNoConfigException;

class FrobouPdoConnection extends FrobouPdoAccess
{
    public function __construct(FrobouPdoConfig $config, $debug = false, Logger $logger = null)
    {
        if (is_null($config)) {
            throw new FrobouNoConfigException();
        }
        $this->config = $config;
        $this->debug = $debug;
        $this->logger = $logger;
    }
}