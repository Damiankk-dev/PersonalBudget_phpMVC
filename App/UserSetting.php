<?php

namespace App;

class UserSetting{

    public string $id;
    public string $name;
    public string $settingType;
    public string $modificationType;
    public $errors = [];

    public function __construct($id, $name, $settingType, $modificationType, $errors = []){
        $this->id = $id;
        $this->name = $name;
        $this->settingType = $settingType;
        $this->modificationType = $modificationType;
        $this->errors = $errors;
    }

}