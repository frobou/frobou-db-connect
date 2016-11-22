## frobou-pdo-access ##

ver 0.X

usage:

    $database = json_decode(file_get_contents(Functions::getDocumentRoot() . '/../rad-src/database.json'));
    $config = new DbConfig();
    $config->setServername($database->server_name)->setPort($database->server_port)
        ->setUsername($database->user_name)->setPassword($database->user_pass)
        ->setDbname($database->db_name)->setServertype($database->server_type);
        $messages = new DbMessages();
        $this->db = new DbAccess($messages, DEBUG, null, $app['logger']);  //$app['logger'] = monolog
                
    protected function selectReturn($sql, $params = [], $fetch_mode = PDO::FETCH_OBJ, $has_count = false) {
        $ret = $this->db->select($sql, $params, $fetch_mode, $has_count);
        if ($ret === null || $ret === false) {
            return $this->db->getError();
        }
        return $ret;
    }
