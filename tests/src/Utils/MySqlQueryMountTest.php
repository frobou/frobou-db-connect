<?php

namespace Frobou\Utils\Sql;

class MySqlQueryMountTest extends \PHPUnit_Framework_TestCase
{

    public function testQuery(){
        $sql = new MySqlQueryMount(['tabela']);
        $sql->addSelectFields(['id','nome'], true)->addWhere(['a'])->addAnd(['a']);
        $this->assertEquals('', $sql->getQuery());
    }

}
