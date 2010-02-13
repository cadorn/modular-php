<?php


class ModularPHP_Sandbox
{
    private static $packages = null;
    private static $activeSandbox = null;
    
    private static $sandboxes = array();

    
    private $packageName = null;
    private $usingPackages = array();


    public static function SetPackages($packages)
    {
        self::$packages = $packages;
    }

    public static function SetActive($package)
    {
        if(is_string($package)) {
            if(!isset(self::$sandboxes[$package])) {
                self::$sandboxes[$package] = new ModularPHP_Sandbox($package);
            }
            self::$activeSandbox = self::$sandboxes[$package];
        } else {
            self::$activeSandbox = $package;
        }
    }

    public static function GetActive()
    {
        return self::$activeSandbox;
    }
    
    
    private static function getPackageInfo($packageName)
    {
        $info = null;
        if(isset(self::$packages['using'][$packageName])) {
            $info = self::$packages['using'][$packageName];
        } else
        if(isset(self::$packages['system'][$packageName])) {
            $info = self::$packages['system'][$packageName];
        } else {
            throw new Exception('Could not find package info for package: ' . $packageName);
        }
        return $info;
    }

    
    private function __construct($packageName)
    {
        $this->packageName = $packageName;
        
        $this->usingPackages = array();
        $packageInfo = self::getPackageInfo($packageName);

        $this->packagePath = realpath($packageInfo["path"]);

/*
        if(isset($packageInfo["descriptor"]->using)) {
            foreach( $packageInfo[1]->using as $name => $using ) {
                $packageName = null;
                if($using->catalog) {
                    $uri = parse_url($using->catalog);
                    $this->usingPackages[$name] = $uri['host'] . dirname($uri['path']) . DIRECTORY_SEPARATOR . $using->name;
                } else
                if($using->location) {
                    $uri = parse_url($using->location);
                    $this->usingPackages[$name] = $uri['host'] . dirname($uri['path']) . (($using->path)?DIRECTORY_SEPARATOR . $using->path:'');
                } else {
                    throw new Exception('Invalid package descriptor for using declaration.');
                }
            }
        }
*/        
    }
        
    public function requireModule($module, $packageName=null)
    {
        if($module{0}=='.') {
            if(!file_exists($packageName)) {
                throw new Exception('Second argument to mp_require() must be __FILE__ when using relative module path: ' + $module);
            }
            
            $file = realpath(dirname($packageName) . DIRECTORY_SEPARATOR . $module . '.php');
            if(!$file || !file_exists($file)) {
                throw new Exception('Module for ID "'.$module.'" in package "'.$this->packageName.'" not found at: ' .dirname($packageName) . DIRECTORY_SEPARATOR . $module . '.php');
            }

            $class = substr($file, strlen($this->packagePath)+5, -4);

            $class = str_replace('/','_', $module);

            require_once($file);

        } else
        if(!$packageName) {
            
            $file = $module . ".php";
            
            $class = str_replace('/','_', $module);

            require($file);
            
        } else {
            
            throw new Exception("Module '".$module."' require with package '".$packageName."' not supported yet!");
            
/*            
            if(!$this->usingPackages[$packageName]) {
                throw new Exception('Package with name "'.$packageName.'" not declared as dependency for package: ' . $this->packageName);
            }
                
            $packageInfo = self::getPackageInfo($this->usingPackages[$packageName]);
            if(!$packageInfo) {
                throw new Exception('Package with ID "'.$this->usingPackages[$packageName].'" referenced by name "'.$packageName.'" from package "'.$this->packageName.'" not found in sea.');
            }
            $file = $packageInfo[0] . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $module . '.php';
            if(!file_exists($file)) {
                throw new Exception('Module for ID "'.$module.'" in package "'.$this->usingPackages[$packageName].'" not found at: ' .$file);
            }

            $class = str_replace('/','_', $module);
            
            $oldActive = self::GetActive();
            self::SetActive($this->usingPackages[$packageName]);
            
            require_once($file);
            
            self::SetActive($oldActive);
*/
        }
                
        return $class;
    }
}
