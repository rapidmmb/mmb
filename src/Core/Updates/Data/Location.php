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


    public function send(array $args = [], ...$namedArgs)
    {
        $this->bot()->send($args + $namedArgs + [
            'type' => 'location',
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'horizontal_accuracy' => $this->horizontalAccuracy,
            'live_period' => $this->livePeriods,
            'heading' => $this->heading,
            'proximity_alert_radius' => $this->proximityAlertRadius,
        ]);
    }

}
