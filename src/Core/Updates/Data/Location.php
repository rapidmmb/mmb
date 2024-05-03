<?php

namespace Mmb\Core\Updates\Data;

use Mmb\Core\Data;

/**
 * @property float  $longitude
 * @property float  $latitude
 * @property ?float horizontalAccuracy
 * @property ?int   $livePeriods
 * @property ?int   $heading
 * @property ?int   $proximityAlertRadius
 */
class Location extends Data
{

    protected function dataCasts() : array
    {
        return [
            'longitude'              => 'float',
            'latitude'               => 'float',
            'horizontal_accuracy'    => 'float',
            'live_period'            => 'int',
            'heading'                => 'int',
            'proximity_alert_radius' => 'int',
        ];
    }

}