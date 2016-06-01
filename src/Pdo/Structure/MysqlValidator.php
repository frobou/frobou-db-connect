<?php

namespace Frobou\Pdo\Structure;

use Frobou\Pdo\Db\DbAccess;
use Frobou\Pdo\Structure\Interfaces\PdoTableStructure;

class MysqlValidator implements PdoTableStructure {

    public function getTableStructure($table_name, DbAccess $db)
    {
        $sql = "describe {$db->getSchema()}.{$table_name}";
        $ret = $db->select($sql);
        if (count($ret) > 0) {
            return $this->getStruct($ret);
        }
        return false;
    }

    private function setTableName($operation, $query)
    {
        switch (strtoupper(trim($operation))) {
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

    private function getStruct($data)
    {
        foreach ($data as $value) {
            if (strpos($value->Extra, 'auto_increment') !== false) {
                continue;
            }
            $ret[$value->Field] = [];
            $ret[$value->Field]['required'] = $this->validateRequired($value);
            $ret[$value->Field]['type'] = $this->validateType($value);
            $ret[$value->Field]['size'] = $this->validateSize($value);
            $ret[$value->Field]['default'] = $value->Default;
        }
        return $ret;
    }

    private function validateRequired($data)
    {
        if ($data->Null == 'NO') {
            return true;
        }
        return false;
    }

    private function validateType($data)
    {
        $ret = '';
        $regex = '/^[a-z]{0,}/';
        if (preg_match_all($regex, $data->Type, $s_ret) !== 0) {
            foreach ($s_ret[0] as $s) {
                $ret .= $s;
            }
        }
        return $ret;
    }

    private function validateSize($data)
    {
        $ret = '';
        $regex = '/[0-9]/';
        if (preg_match_all($regex, $data->Type, $s_ret) !== 0) {
            foreach ($s_ret[0] as $s) {
                $ret .= $s;
            }
        }
        return (int) $ret;
    }

}
