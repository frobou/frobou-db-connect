<?php

namespace Frobou\Db;

use Frobou\Db\Exceptions\FrobouConfigErrorException;
use Frobou\Validator\FrobouValidator;

class FrobouDbConfig
{
    private $settings;
    private $db_names = [];

    public function __construct(\stdClass $settings)
    {
        if (!isset($settings->connections)) {
            throw new FrobouConfigErrorException();
        }
        $validator = new FrobouValidator();
        $fields = ['server_type', 'server_host', 'server_port','db_name', 'user_name', 'user_pass'];
        $opt = ['server_charset', 'attributes'];
        $sgdbs = ['mysql', 'postgree', 'sqlite'];
        foreach ($settings->connections as $key => $value) {
            $data['struct'] = [];
            array_push($data['struct'], $value, $fields, $opt);
            $v = $validator->validate(['struct'],$data, true);
            if ($v !== true){
                throw new FrobouConfigErrorException($v['struct']);
            }
            foreach ($fields as $fld) {
                if (!isset($value->{$fld})) {
                    throw new FrobouConfigErrorException('Config syntax error');
                }
            }
            if (!in_array(strtolower($settings->connections->{$key}->server_type), $sgdbs)) {
                throw new FrobouConfigErrorException('Server type is not allowed');
            }
            array_push($this->db_names, $key);
            unset($data);
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