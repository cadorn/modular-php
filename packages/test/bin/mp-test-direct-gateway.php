<?php

/*PINF_MACRO[LoadCommandEnvironment]*/

// TODO: This should be implemented as a pinf "service" instead of a command

$class = mp_require('ModularPHP/Test/Responder');
$obj = new $class();
$obj->respond($RESPONDER_ARGS);
