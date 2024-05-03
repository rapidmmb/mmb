<?php

namespace Mmb\Core\Updates\Data;

use Mmb\Core\Data;

/**
 * @property Location $location
 * @property string   $title
 * @property string   $address
 * @property ?string  $foursquareId
 * @property ?string  $foursquareType
 * @property ?string  $googlePlaceId
 * @property ?string  $googlePlaceType
 */
class Venue extends Data
{

    protected function dataCasts() : array
    {
        return [
            'location'          => Location::class,
            'title'             => 'string',
            'address'           => 'string',
            'foursquare_id'     => 'string',
            'foursquare_type'   => 'string',
            'google_place_id'   => 'string',
            'google_place_type' => 'string',
        ];
    }

}