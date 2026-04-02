<?php

namespace App\Support;

class CountryList
{
    public static function all()
    {
        $countries = [
            'CA' => 'Canada',
            'US' => 'United States',
        ];

        asort($countries);

        return $countries;
    }
}
