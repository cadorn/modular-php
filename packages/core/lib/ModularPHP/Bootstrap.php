<?php

require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'mp_require.php');
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR .  'Sandbox.php';


class ModularPHP_Bootstrap
{
    const DEBUG = false;

    private $seaPath = null;
    private $packageName = null;
    
    private $packages = array();


    public static function Program($seaPath, $packageName)
    {
        if(self::DEBUG) print('[Debug] ModularPHP_Bootstrap::Program("'.$packageName.'")'."\n");
        
        $bootstrap = new ModularPHP_Bootstrap($seaPath, $packageName);
        $bootstrap->bootstrap();
    }
    
    private function __construct($seaPath, $packageName)
    {
        $this->seaPath = $seaPath;
        $this->packageName = $packageName;
    }
    
    private function bootstrap()
    {
        $this->packages = array('system'=>array(), 'using'=>array());

        if(self::DEBUG) print('[Debug]   ->scanPackages("'.$this->seaPath.'")'."\n");

        $this->scanPackages($this->seaPath);

        if(self::DEBUG) print('[Debug]   ->scanUsingPackages("'.$this->seaPath.DIRECTORY_SEPARATOR.'using'.'")'."\n");

        $this->scanUsingPackages($this->seaPath . DIRECTORY_SEPARATOR . 'using');

        if(!$this->packages['system'][$this->packageName]) {
            throw new Exception('Package "'.$this->packageName.'" not found in system packages ('.implode(',', array_keys($this->packages['system'])).')!');
        }
        
        // TODO: Set timezone based on packageDatum
        date_default_timezone_set('America/Vancouver');
        
        
        $includePath = array();
        if(self::DEBUG) print('[Debug]   ->assembleIncludePath()'."\n");
        $this->assembleIncludePath($this->packageName, $includePath);

        set_include_path(
            implode(PATH_SEPARATOR, $includePath) . 
            PATH_SEPARATOR . 
            get_include_path()
        );

        ModularPHP_Sandbox::setPackages($this->packages);
        ModularPHP_Sandbox::setActive($this->packageName);
    }
    
    private function assembleIncludePath($packageName, &$includePath)
    {
        $packageInfo = null;
        if(isset($this->packages['using'][$packageName])) {
            $packageInfo = $this->packages['using'][$packageName];
        } else
        if(isset($this->packages['system'][$packageName])) {
            $packageInfo = $this->packages['system'][$packageName];
        } else {
            throw new Exception('Could not find package info for package: ' . $packageName);
        }

        if(self::DEBUG) print('[Debug]     Added package "'.$packageName.'" at path: '.$packageInfo[0] . DIRECTORY_SEPARATOR . 'lib'."\n");
                
        $includePath[] = $packageInfo[0] . DIRECTORY_SEPARATOR . 'lib';
        
        if(isset($packageInfo[1]->using)) {
            foreach( $packageInfo[1]->using as $using ) {
                $packageName = null;
                if($using->catalog) {
                    $uri = parse_url($using->catalog);
                    $packageName = $uri['host'] . dirname($uri['path']) . DIRECTORY_SEPARATOR . $using->name;
                } else
                if($using->location) {
                    $uri = parse_url($using->location);
                    $packageName = $uri['host'] . dirname($uri['path']) . (($using->path)?DIRECTORY_SEPARATOR . $using->path:'');
                } else {
                    throw new Exception('Invalid package descriptor for using declaration.');
                }

                $this->assembleIncludePath($packageName, $includePath);
            }
        }
    }

    private function scanPackages($path)
    {
        $path .= DIRECTORY_SEPARATOR . 'packages';
        
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

                    $this->packages['system'][$datum->name] = array($entry->getPathname(), $datum);
                    
                    $this->scanPackages($entry->getPathname());
                }
            }
        }
    }


    private function scanUsingPackages($basePath, $subPath='')
    {
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

