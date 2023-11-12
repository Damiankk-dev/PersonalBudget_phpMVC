<?php

namespace App;

class UserSetting{

    public string $name;
    public string $settingType;
    public string $id = "";
    public $errors = [];

    public function __construct($name, $settingType, $id = "", $errors = []){
        $this->name = $name;
        $this->settingType = $settingType;
        $this->id = $id;
        $this->errors = $errors;
    }

}