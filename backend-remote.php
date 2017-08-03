<?php
error_reporting(E_ERROR | E_PARSE);

header('content-type: application/json; charset=utf-8');

function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        $merda = @include_once($filename);
        return ob_get_clean();
    }
    return false;
}


$data = get_include_contents("backend.php");

echo $_GET['callback'] . '('.$data.')';
