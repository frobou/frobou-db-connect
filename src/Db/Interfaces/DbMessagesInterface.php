<?php

namespace Frobou\Db\Interfaces;

interface DbMessagesInterface {

    /**
     * @param string $more
     * @return string
     */
    public function getGeneric($more);
    
    /**
     * 
     * @return string
     */
    public function getNotInserted();
    
    /**
     * 
     * @return string
     */
    public function getNotDeleted();
    
    /**
     * 
     * @return string
     */
    public function getNotUpdated();
}
