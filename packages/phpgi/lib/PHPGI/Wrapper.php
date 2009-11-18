<?php

mp_require('./Env', __FILE__);
mp_require('./Util', __FILE__);

class PHPGI_Wrapper
{
    private $app = null;
    
    
    public function setApp(PHPGI_App $app)
    {
        $this->app = $app;
    }   
    
    public function run()
    {
        $env = new PHPGI_Env();
        $response = $this->app->run($env);
        
        if(!$response['status']) {
            throw new Exception('No response status set by app.');
        }

        header('HTTP/1.1 ' . $response['status'] . ' ' . PHPGI_Util::$HTTP_STATUS_CODES[$response['status']]);
        
        if(isset($response['headers'])) {
            foreach( $response['headers'] as $name => $value ) {
                header($name . ': ' . $value, true);
            }
        }
        
        if(isset($response['body'])) {
            print $response['body'];
        }
    }


    public static function GetHelper($name)
    {
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . $name . '.php';
        require_once($file);
        $class = 'PHPGI_Helper_' . $name;
        return new $class();
    }   
        
    public static function GetMiddleware($name)
    {
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Middleware' . DIRECTORY_SEPARATOR . $name . '.php';
        require_once($file);
        $class = 'PHPGI_Middleware_' . $name;
        return new $class();
    }   
        
}
