<?php

function mp_require($module, $packageName=null)
{
    return ModularPHP_Sandbox::GetActive()->requireModule($module,  $packageName);
}
