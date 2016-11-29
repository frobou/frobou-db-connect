<?php
/**
 * Created by PhpStorm.
 * User: suporte09
 * Date: 29/11/16
 * Time: 14:36
 */

namespace Frobou\Db;


class FrobouDbUtils
{
    public function bindParams($data, $except = [])
    {
        $params = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $except)) {
                continue;
            }
            switch (strtoupper(gettype($key))) {
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
        return $params;
    }
}