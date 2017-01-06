<?php

namespace Frobou\Pdo\Db;

class FrobouDbUtils
{
    public function bindParams($data, $except = [])
    {
        $params = [];
        $fields = '';
        $named = '';
        foreach ($data as $key => $value) {
            if (in_array($key, $except)) {
                continue;
            }
            $fields .= "{$key}, ";
            $named .= ":{$key}, ";
            switch (strtoupper(gettype($value))) {
                case 'BOOLEAN':
                    array_push($params, ['param' => ":{$key}", 'value' => $value, 'type' => \PDO::PARAM_BOOL]);
                    break;
                case 'INTEGER':
                    array_push($params, ['param' => ":{$key}", 'value' => $value, 'type' => \PDO::PARAM_INT]);
                    break;
                case 'NULL':
                    array_push($params, ['param' => ":{$key}", 'value' => $value, 'type' => \PDO::PARAM_NULL]);
                    break;
                default:
                    array_push($params, ['param' => ":{$key}", 'value' => $value, 'type' => \PDO::PARAM_STR]);
            }
        }
        array_unshift($params, substr($named, 0, strlen($named) -2));
        array_unshift($params, substr($fields, 0, strlen($fields) -2));
        return $params;
    }
}
