<?php

namespace Frobou\Pdo\Db\Interfaces;

interface DbMessagesInterface {

    public function getGeneric($more);
    
    public function getNotInserted();
    
    public function getNotDeleted();
    
    public function getNotUpdated();
}
