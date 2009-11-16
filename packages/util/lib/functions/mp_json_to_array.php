<?php

function mp_json_to_array($data) {
    
    if(is_string($data)) {
        $data = json_decode($data);
    }
    
    if(is_object($data)) {
        $data = (array)$data;
    }
    
    if(is_array($data)) {
        foreach( $data as $name => $value ) {
            if(is_object($value) || is_array($value)) {
                $data[$name] = mp_json_to_array($value);
            }
        }
    }
    
    return $data;     
}
