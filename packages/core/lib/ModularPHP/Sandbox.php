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

    public static function SetActive($packageName)
    {
        if(!self::$sandboxes[$packageName]) {
            self::$sandboxes[$packageName] = new ModularPHP_Sandbox($packageName);
        }
        self::$activeSandbox = self::$sandboxes[$packageName];
    }

    public static function GetActive()
    {
        return self::$activeSandbox;
    }
    
    
    private static function getPackageInfo($packageName)
    {
        $info = self::$packages['using'][$packageName];
        if(!$info) $info = self::$packages['system'][$packageName];
        return $info;
    }

    
    private function __construct($packageName)
    {
        $this->packageName = $packageName;
        
        $this->usingPackages = array();
        $packageInfo = self::getPackageInfo($packageName);
        if($packageInfo[1]->using) {
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
    }
        
    public function requireModule($module, $packageName)
    {
        if($packageName===null) {
            
            throw new Exception("NYI: mp_require() without package argument");
            
        } else {
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
            require_once($file);
        }

    }

}
