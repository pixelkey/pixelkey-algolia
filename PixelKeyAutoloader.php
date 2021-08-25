<?php

spl_autoload_register('pixelkey_autoloader');

function pixelkey_autoloader($classname) {
    $dir = dirname(__FILE__);
    $parts = explode('\\', $classname);
    $location = $dir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';

    //If it's not in our namespace, other autoloaders should handle them
    if(!in_array('PixelKey', $parts)){
        return false;
    }

    $location = str_replace('PixelKey' . DIRECTORY_SEPARATOR , '', $location);

    if(file_exists($location)){
        return include $location;
    } else {
        return false;
    }
}
