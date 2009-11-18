<?php

class PHPGI_App
{
    protected $app = null;
    
    public function app($app)
    {
        if(!is_object($app)) {
            $returnApp = PHPGI_Wrapper::getMiddleware('Return');
            $returnApp->setResponse($app);
            $app = $returnApp;
        }
        $this->app = $app;
        return $this;
    }

    public function run($env)
    {
        return $this->app->run($env);
    }
}
