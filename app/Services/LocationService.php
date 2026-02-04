<?php

namespace App\Services;

use DateTimeZone;

class LocationService
{
    public static function getTimezonesForCountry($countryCode)
    {
        return DateTimeZone::listIdentifiers(
            timezoneGroup: DateTimeZone::PER_COUNTRY,
            countryCode: $countryCode,
        );
    }
}
