<?php

class PHPGI_Env
{
    public function get($name)
    {
        switch($name) {
            
            // @see http://jackjs.org/jsgi-spec.html
            // A typical PHPGI setup is as follows:
            //  DocumentRoot: <package>/vhosts/www
            //  Document root files:
            //      <package>/vhosts/www/.htaccess - directs all non-static requests to ./index.php
            //      <package>/vhosts/www/index.php - the "application" object
            //  in which case:
            //      SCRIPT_NAME = /index.php
            //      PATH_INFO = URL Requested by client
            
            case 'SCRIPT_NAME':
                return $_SERVER['SCRIPT_NAME'];
            case 'PATH_INFO':
                $uri = $_SERVER['REQUEST_URI'];
                $parts = explode('?', $uri);
                return $parts[0];
                
                
            case 'QUERY_STRING':
            	return $_SERVER['QUERY_STRING'];           
        }
    }

    public function getGet($name=null)
    {
        if($name!==null) {
            if(!isset($_GET[$name])) {
                return null;
            }
            return $_GET[$name];
        }
        return $_GET;
    }    
}
