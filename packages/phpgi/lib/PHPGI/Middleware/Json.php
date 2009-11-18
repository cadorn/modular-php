<?php

class PHPGI_Middleware_Json extends PHPGI_App
{
    public function run($env)
    {
        $response = $this->app->run($env);
        
//        $response['headers']['Content-Type'] = 'application/json';
        $response['body'] = json_encode($response['body']);

        return $response;
    }
}
