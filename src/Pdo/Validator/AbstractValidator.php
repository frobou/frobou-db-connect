<?php

namespace Frobou\Pdo\Validator;

abstract class AbstractValidator {

    protected function validateNotEmpty(){
        
    }

    protected function validateString(){
        
    }
    
    protected function validateInteger(){
        
    }
    
    protected function validateBoolean(){
        
    }
    
    protected function validateUnique(){
        
    }

    protected function jsonValidateStructure($json, $header, $optional = [])
    {
        $a['not_found'] = [];
        $a['not_allowed'] = [];
        $has = false;
        foreach ($header as $value) {
            if (!isset($json->{$value})) {
                $has = true;
                array_push($a['not_found'], $value);
            }
        }
        foreach ($json as $key => $value) {
            if (!in_array($key, $header)) {
                if (!in_array($key, $optional)) {
                    $has = true;
                    array_push($a['not_allowed'], $key);
                }
            }
        }
        if ($has === true) {
            return $a;
        }
        return true;
    }
}
