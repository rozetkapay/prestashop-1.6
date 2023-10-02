<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Payment\RozetkaPay\Model;

class Model {
    public function __construct($data = []) {
        
        foreach ($this as $key => $value) {
            if(isset($data[$key])){
                $this->{$key} = $data[$key];
            }
        }
        
    }
}
