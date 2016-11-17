<?php

namespace Frobou\Pdo\Db;

use Frobou\Pdo\Exceptions\FrobouConfigErrorException;

class FrobouPdoConfig
{
    /**
     * @var \stdClass
     */
    private $settings;
    private $db_names = [];

    public function __construct(\stdClass $settings)
    {
        if (!isset($settings->connections)){
            throw new FrobouConfigErrorException();
        }
        foreach ($settings->connections as $key => $value){
            array_push($this->db_names, $key);
        }
        if (count(get_object_vars($settings->connections)) === 1){
                $settings->default = $settings->connections->{$this->db_names[0]};
        } else {
            if (!isset($settings->default)){
                throw new FrobouConfigErrorException('Default db is not informed');
            }
        }
        $this->settings = $settings;
    }

    public function getAttributes()
    {
    }

    public function getDbNames(){
        return $this->db_names;
    }

    public function getDefaultDb()
    {
        return $this->settings->default;
    }

    public function verifica_se_banco_existe($db_name)
    {
        if (!in_array($this->db_names, $db_name)){
            throw new FrobouConfigErrorException('Database not found');
        }
        return true;
    }

}