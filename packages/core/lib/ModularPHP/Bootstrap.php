<?php

require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'mp_require.php');
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR .  'Sandbox.php';


class ModularPHP_Bootstrap
{
    const DEBUG = false;

    private $programHomePath = null;
    private $packageName = null;
    
    private $packages = array();
    
    private static $options = array();
    
    public static function SetOption($name, $value) {
        self::$options[$name] = $value;
    }

    public static function GetOption($name) {
        if(!array_key_exists($name, self::$options)) {
            throw new Exception("Option not set: " . $name);
        }
        return self::$options[$name];
    }

    public static function Program($programHomePath, $packageName, $extraPackages=null)
    {
        if(self::DEBUG) print('[Debug] ModularPHP_Bootstrap::Program("'.$packageName.'")'."\n");
        
        $bootstrap = new ModularPHP_Bootstrap($programHomePath, $packageName);
        $bootstrap->bootstrap($extraPackages);
    }
    
    private function __construct($programHomePath, $packageName)
    {
        $this->programHomePath = $programHomePath;
        $this->packageName = $packageName;
    }
    
    private function bootstrap($extraPackages)
    {
        $this->packages = array('system'=>array(), 'using'=>array());

        if(self::DEBUG) print('[Debug]   ->scanPackages("'.$this->programHomePath.'")'."\n");

        $this->scanPackages($this->programHomePath);

        if(self::DEBUG) print('[Debug]   ->scanUsingPackages("'.$this->programHomePath.DIRECTORY_SEPARATOR.'using'.'")'."\n");

        $this->scanUsingPackages($this->programHomePath . DIRECTORY_SEPARATOR . 'using');

        if(!$this->packages['system'][$this->packageName]) {
            throw new Exception('Package "'.$this->packageName.'" not found in system packages ('.implode(',', array_keys($this->packages['system'])).')!');
        }

        if($extraPackages) {
            foreach( $extraPackages as $type => $packages ) {
                if($type=="system") {
                    foreach( $packages as $package ) {
                        $this->scanPackages($package["path"]);
                    }
                } else {
                    throw new Exception("Package type not supported: " . $type);
                }
            }
        }

        // TODO: Set timezone based on packageDatum
        date_default_timezone_set('America/Vancouver');
        
        
        $includePath = array();
        if(self::DEBUG) print('[Debug]   ->assembleIncludePath()'."\n");
        $this->assembleIncludePath($includePath);
/*
        set_include_path(
            implode(PATH_SEPARATOR, $includePath) . 
            PATH_SEPARATOR . 
            get_include_path()
        );
*/
        set_include_path(
            implode(PATH_SEPARATOR, $includePath)
        );

        ModularPHP_Sandbox::setPackages($this->packages);
        ModularPHP_Sandbox::setActive($this->packageName);
    }

    private function assembleIncludePath(&$includePath)
    {
        if(!isset($this->packages['system'])) {
            return;
        }
        foreach( $this->packages['system'] as $name => $packageInfo ) {

            if(self::DEBUG) print('[Debug]     Added package at path: '.$packageInfo["path"] . DIRECTORY_SEPARATOR . 'lib'."\n");

            $includePath[] = $packageInfo["path"] . DIRECTORY_SEPARATOR . 'lib';
        }
    }

    private function scanPackages($path)
    {
        if(!file_exists($path) || !is_dir($path)) {
            return;
        }
        
        $file = $path . DIRECTORY_SEPARATOR . 'package.json';
        if(file_exists($file)) {
            if(self::DEBUG) print('[Debug]     Found package at: '.$file."\n");

            $descriptor = json_decode(file_get_contents($file), true);
            if(!$descriptor) {
                throw new Exception('JSON Error ['.$this->getJsonError(json_last_error()).'] in file: '.$file);
            }
            
            // add the program package with own name, all other based on directory name
            $name = basename($path);
            if($path==$this->programHomePath) {
                $name = $descriptor["name"];
            }

            $this->packages['system'][$name] = array(
                'path' => $path,
                'descriptor' => $descriptor
            );
        }
        
        $path .= DIRECTORY_SEPARATOR . 'packages';
        if(!file_exists($path)) return;

        foreach( new DirectoryIterator($path) as $entry ) {
            if(!$entry->isDot() && $entry->isDir()) {
                $this->scanPackages($entry->getPathname());
            }
        }
    }


    private function scanUsingPackages($basePath, $subPath='')
    {
/*
        $path = $basePath . (($subPath)?DIRECTORY_SEPARATOR . $subPath : '');
        if(!file_exists($path) || !is_dir($path)) {
            return;
        }

        foreach( new DirectoryIterator($path) as $entry ) {
            if(!$entry->isDot() && $entry->isDir()) {
                $file = $entry->getPathname() . DIRECTORY_SEPARATOR . 'package.json';
                if(file_exists($file)) {
                    if(self::DEBUG) print('[Debug]     Found package at: '.$file."\n");
                    
                    $datum = json_decode(file_get_contents($file));
                    if(!$datum) {
                        throw new Exception('JSON Error ['.$this->getJsonError(json_last_error()).'] in file: '.$file);
                    }
                    
                    $this->packages['using'][$subPath.DIRECTORY_SEPARATOR.$entry->getBasename()] = array($entry->getPathname(), $datum);
                } else {
                    $this->scanUsingPackages($basePath, (($subPath)?$subPath.DIRECTORY_SEPARATOR:'').$entry->getBasename());
                }
            }
        }
*/        
    }
    
    private function getJsonError($number)
    {
        switch($number)
        {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_NONE:
                return 'No errors';
        }
    }    
    
}

/*    
    private function getSeaPath()
    {
        // This file is always located at: <seaPath>/using/github.com/cadorn/modular-php/raw/master/core/lib/ModularPHP/Bootstrap.php
        $parts = explode(DIRECTORY_SEPARATOR, __FILE__);
        return implode(DIRECTORY_SEPARATOR, array_slice($parts, 0, sizeof($parts)-5));
    }
*/

