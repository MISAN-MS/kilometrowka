<?php

require_once 'lib/DB.php';

abstract class Controller
{
    protected $db;
    protected $table;
    protected static $attributes = [];
    protected $result;

    public static function instance()
    {
        $page = !empty($_GET['page']) ? $_GET['page'] : null;
        $action = !empty($_GET['action']) ? $_GET['action'] : 'index';
        $id = !empty($_GET['id']) ? $_GET['id'] : null;
        if(null !== $page) {
            $page = ucfirst($page);
            $pageCtrl = new $page;
            $pageCtrl->$action();
        }
    }

    public static function render($attributes = [])
    {
        if(count($attributes) > 0) {
            self::$attributes = array_merge(self::$attributes, $attributes);
        }
        foreach (self::$attributes as $var => $value) {
            $$var = $value;
        }
    }

    protected function init()
    {
        $this->db = new DB();
    }

    protected function addAttributes($key, $value)
    {
        self::$attributes[$key] = $value;
    }
}