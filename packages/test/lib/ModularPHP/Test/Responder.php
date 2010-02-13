<?php

class ModularPHP_Test_Responder {

    public function respond($args) {
        
        // this module has (or rather will have) two modes of operation:
        //  1) A global handler that will locate packages to test via the default system-wide PINF DB
        //  2) A program handler that will locate packages to test for a specific program

        // TODO: Verify $args["accessKey"] in ~/pinf/config/credentials.js

        $implements_uri = "http://registry.pinf.org/cadorn.org/github/modular-php/packages/test/@meta/gateway/direct/0.1.0";

        // Global handler
        $basePath = ModularPHP_Bootstrap::GetOption('pinfHomePath') . "/packages/registry.pinf.org/" . $args["uid"] . "/" . $args["revision"] . "/";
        $file = $basePath . "package.json";
        if(!file_exists($file)) {
            throw new Exception("File not found at: " . $file);
        }
        $descriptor = json_decode(file_get_contents($file), true);
        if(!isset($descriptor["implements"]) || !isset($descriptor["implements"][$implements_uri])) {
            throw new Exception("Package does not implement: " . $implements_uri);
        }
        if(!isset($descriptor["implements"][$implements_uri]["expose"])) {
            throw new Exception("Package does not provide 'expose' property in implementation declaration");
        }
        $expose = $descriptor["implements"][$implements_uri]["expose"];
        $exposed = false;
        foreach( $expose as $path ) {
            if(substr($args["path"], 0, strlen($path))==$path) {
                $exposed = true;
                break;
            }
        }
        if(!$exposed) {
            throw new Exception("Requested path '" . $args["path"] . "' not exposed in implementation declaration");
        }
        
        $file = realpath($basePath . $args["path"]);
        
        if(is_dir($file)) {
            
            foreach( new DirectoryIterator($file) as $item ) {
                if(!$item->isDot() && substr($item->getFilename(),0,5)!='.tmp_') {
                    print('<p><a href="' . $item->getFilename() . '">' . $item->getFilename() . '</a></p>');
                }
            }

        } else {
            require_once($file);
        }
    }
}
