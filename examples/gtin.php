<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\Gtin\Gtin;

try {
    if (Gtin::check('78935761')->isValid()) {
        echo "Valido";
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}




