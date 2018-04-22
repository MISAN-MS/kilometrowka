<?php

if (!function_exists('conf')) {
    function conf($key) {
        $config = include './config/config.php';
        return $config[$key];
    }
}

if (!function_exists('app')) {
    function app() {
        render(conf('START_VIEW'));
    }
}

if (!function_exists('render')) {
    function render($view) {
        if(file_exists('./www/' . $view . '.php')) {
            require_once './www/' . $view . '.php';
        }
    }
}

if (!function_exists('asset')) {
    function asset($asset) {
        if(file_exists('./assets/' . $asset)) {
            return '/assets/' . $asset;
        }
    }
}