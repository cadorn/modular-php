<?php

class PHPGI_Helper_PathInfoToPaths
{
    public function help($env, $options)
    {
        if(!$options || !$options['base.path']) {
            throw new Exception('The "base.path" option must be set.');
        }
        if(!isset($options['default.file.name'])) $options['default.file.name'] = 'Index';
        if(!isset($options['file.extensions'])) $options['file.extensions'] = array('php');
        
        $uri = $env->get('PATH_INFO');

        if($uri=='/') {
            $uri = $options['default.file.name'];
        } else {
            
            if(strpos($uri, '..')!==false) {
                $uri = $options['default.file.name'];
            } else {
            
                $uri = substr($uri, 1);
                
                if(substr($uri, -1, 1)=='/') {
                    $uri .= $options['default.file.name'];
                }
            }
        }
        
        $paths = array();
        foreach( $options['file.extensions'] as $extension ) {
            $paths[$extension] = $options['base.path'] . DIRECTORY_SEPARATOR . $uri . '.' . $extension;
        }
        return $paths;
    }
}
