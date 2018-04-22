<?php

include_once './helpers/functions.php';

function controllerLoad($name) {
    if(file_exists('controller/'.ucfirst($name).'.php')) {
        require_once 'controller/'.ucfirst($name).'.php';
    }
}

spl_autoload_register('controllerLoad');