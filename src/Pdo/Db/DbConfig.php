<?php

namespace Frobou\Pdo\Db;

final class DbConfig {

    private $servertype;
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $port;
    private $attributes = [];
    private $charset = 'utf8';

    function getServertype()
    {
        return $this->servertype;
    }

    function getServername()
    {
        return $this->servername;
    }

    function getUsername()
    {
        return $this->username;
    }

    function getPassword()
    {
        return $this->password;
    }

    function getDbname()
    {
        return $this->dbname;
    }

    function getPort()
    {
        return $this->port;
    }

    function getAttributes()
    {
        return $this->attributes;
    }

    function getCharset()
    {
        return $this->charset;
    }

    function setServertype($servertype)
    {
        $this->servertype = $servertype;
        return $this;
    }

    function setServername($servername)
    {
        $this->servername = $servername;
        return $this;
    }

    function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    function setDbname($dbname)
    {
        $this->dbname = $dbname;
        return $this;
    }

    function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Adiciona atributos
     * @param array $attributes {['param' => 'param-name', 'value' => 'the-value']}
     * @return \Frobou\DbConfig
     */
    function setAttributes(array $attributes)
    {
        array_push($this->attributes, $attributes);
        return $this;
    }

    /**
     * 
     * @param type $charset
     * @return DbConfig
     */
    function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

}
