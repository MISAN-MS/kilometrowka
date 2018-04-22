<?php

class Place extends Controller
{
    public function __construct()
    {
        parent::init();
        $this->table = strtolower(__CLASS__);
        self::render(['view' => 'app/place']);
    }

    public function index()
    {
        render('app/place');
    }

}