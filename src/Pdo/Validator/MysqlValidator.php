<?php

namespace Frobou\Pdo\Validator;

use Frobou\Pdo\Db\DbAccess;
use Frobou\Pdo\Validator\Interfaces\PdoValidatorInterface;

class MysqlValidator implements PdoValidatorInterface {
    
    private function setTableName($operation, $query)
    {
        switch (strtoupper(trim($operation))){
            case 'INSERT':
                $regex = '/ into ([^ ]+)/';
                break;
            case 'UPDATE':
                $regex = '/^update ([^ ]+)/';
                break;
            default :
                return true;
        }
        if (preg_match($regex, $query, $table) !== 1) {
            return false;
        }
        return $table;
    }

    private function Validate($data){
        $required = [];
        $size = [];
        //DATA_TYPE
        foreach ($data as $value){
            switch (strtoupper($value->DATA_TYPE)){
                case 'VARCHAR':
                    break;
                case 'DATETIME':
                    break;
                case 'TINYINT':
                    break;
                default :
                    break;
            }

            if ($value->IS_NULLABLE == 'NO'){
                array_push($required, $value->COLUMN_NAME);
            }
            if ($value->CHARACTER_MAXIMUM_LENGTH != null){
                if (!isset($type['string'])){
                    $type['string'] = [];
                }
                array_push($type['string'], $value->COLUMN_NAME);
                $size[$value->COLUMN_NAME] = $value->CHARACTER_MAXIMUM_LENGTH;
            } else if ($value->NUMERIC_PRECISION != null){
                if (!isset($type['integer'])){
                    $type['integer'] = [];
                }
                array_push($type['integer'], $value->COLUMN_NAME);
                $num = str_repeat(9, $value->NUMERIC_PRECISION);
                $size[$value->COLUMN_NAME] = $num;
            }
        }
        return ['required' => $required, 'type' => $type, 'size' => $size];
    }
    
    public function getTableStruct($operation, $query, DbAccess $db){
        $valid = $this->setTableName($operation, $query);
        if ($valid === false) {
            return false;
        }
        $sql = "select COLUMN_NAME, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, "
            . "NUMERIC_PRECISION, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_TYPE "
            . "from information_schema.columns cols "
            . "where table_schema = '{$db->getSchema()}' and TABLE_NAME = '{$valid[1]}' "
            . "and cols.EXTRA not like '%auto_increment%'";
        $table = $db->select($sql);
        if (count($table) > 0) {
            return $this->Validate($table);
        }
        return false;
    }
    
}
