<?php

class PHPGI_Middleware_Json extends PHPGI_App
{
    public function run($env)
    {
        $response = $this->app->run($env);
        
//        $response['headers']['Content-Type'] = 'application/json';


        // JSONP
        if($jsoncallback = $env->getGet('jsoncallback')) {
            
            $response['body'] = $jsoncallback . '(' . json_encode($response['body']) . ')';

        } else {
            // Plain JSON
            
            $response['body'] = json_encode($response['body']);
        }

        return $response;
    }
}
