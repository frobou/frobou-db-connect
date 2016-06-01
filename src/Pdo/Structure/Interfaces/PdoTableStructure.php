<?php

namespace Frobou\Pdo\Validator\Structure;

use Frobou\Pdo\Db\DbAccess;

interface PdoTableStructure {

    public function getTableStructure($table_name, DbAccess $db);
}
