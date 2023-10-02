<?php

namespace Payment\RozetkaPay\Model;

class Enum {
    
    static function get($value){
        return $this->{$value};
    }
    
}
