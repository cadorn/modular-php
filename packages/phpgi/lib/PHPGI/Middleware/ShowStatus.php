<?php

mp_require('../Util', __FILE__);

class PHPGI_Middleware_ShowStatus extends PHPGI_App
{
    public function run($env)
    {
        $response = $this->app->run($env);
        
        if($response['status']>=400 && !$response['body']) {

            $response['headers']['Content-Type'] = 'text/html';            
            $response['body'] = $response['status'] . ': ' . PHPGI_Util::$HTTP_STATUS_CODES[$response['status']];
            
        }

        return $response;
    }
}
