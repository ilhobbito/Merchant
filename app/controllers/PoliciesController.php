<?php
namespace App\Controllers;

require '../vendor/autoload.php';
class PoliciesController
{
    // These are only temporary and exist purely because Facebook requires terms and privacy urls to work
    public function __construct(){

    }

    public function terms(){
        require_once '../app/views/policies/terms.php';
    }

    public function privacy(){
        require_once '../app/views/policies/privacy.php';
    }

}