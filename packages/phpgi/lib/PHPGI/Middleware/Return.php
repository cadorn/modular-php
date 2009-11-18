<?php

class PHPGI_Middleware_Return extends PHPGI_App
{
    private $response = null;
    
    public function setResponse($response)
    {
        $this->response = $response;
    }
    
    public function run($env)
    {
        return $this->response;
    }
}
