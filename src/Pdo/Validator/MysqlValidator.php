<?php

namespace Frobou\Pdo\Validator;

use Frobou\Pdo\Validator\Interfaces\PdoValidatorInterface;

class MysqlValidator implements PdoValidatorInterface {

    public function setTableName($operation, $query)
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
    
    public function Validate($data){
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
//        var_dump($data);die;
        return ['required' => $required, 'type' => $type, 'size' => $size];
    }
}
