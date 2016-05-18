<?php

namespace Frobou\Pdo\Db;

use Frobou\Pdo\Db\Interfaces\DbMessagesInterface;


class DbMessages implements DbMessagesInterface {

    public function getGeneric($more)
    {
        return "Error on prepare to {$more}";
    }
    
    public function getNotInserted(){
        return 'Nothing was inserted';
    }
    
    public function getNotDeleted(){
        return 'Nothing was deleted';
    }
    
    public function getNotUpdated(){
        return 'Nothing was updated';
    }

}
