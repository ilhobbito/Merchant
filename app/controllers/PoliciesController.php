<?php
namespace App\Controllers;

require '../vendor/autoload.php';
class PoliciesController
{
    public function __construct(){

    }

    public function terms(){
        require_once '../app/views/policies/terms.php';
    }

    public function privacy(){
        require_once '../app/views/policies/privacy.php';
    }

}