<?php

namespace Frobou\Pdo\Validator\Interfaces;

use Frobou\Pdo\Db\DbAccess;

interface PdoValidatorInterface {

    public function getTableStruct($operation, $query, DbAccess $db);
    
}
