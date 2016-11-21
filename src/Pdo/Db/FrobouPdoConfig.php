<?php

namespace Frobou\Pdo\Db;

use Frobou\Pdo\Exceptions\FrobouConfigErrorException;

class FrobouPdoConfig
{
    private $settings;
    private $db_names = [];

    public function __construct(\stdClass $settings)
    {
        if (!isset($settings->connections)) {
            throw new FrobouConfigErrorException();
        }
        $fields = ['server_type', 'server_host', 'server_port','server_charset', 'db_name', 'user_name', 'user_pass'];
        $sgdbs = ['mysql', 'postgree', 'sqlite'];
        foreach ($settings->connections as $key => $value) {
            foreach ($fields as $fld) {
                if (!isset($value->{$fld})) {
                    throw new FrobouConfigErrorException('Config syntax error');
                }
            }
            if (!in_array(strtolower($settings->connections->{$key}->server_type), $sgdbs)) {
                throw new FrobouConfigErrorException('Server type is not allowed');
            }
            array_push($this->db_names, $key);
        }
        if (count(get_object_vars($settings->connections)) === 1) {
            $settings->default = $this->db_names[0];
        } else {
            if (!isset($settings->default)) {
                throw new FrobouConfigErrorException('Default db is not informed');
            }
        }
        $this->settings = $settings;
    }

    public function getDbNames()
    {
        return $this->db_names;
    }

    public function getDefaultDb()
    {
        return $this->settings->default;
    }

    public function getSettings(){
        return $this->settings;
    }


}