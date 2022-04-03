<?php

namespace App\Utility;

class Geocode
{
    public function __construct() {
        //Return GB default for the purpose of the test
        return ['country' => 'GB'];
    }
}