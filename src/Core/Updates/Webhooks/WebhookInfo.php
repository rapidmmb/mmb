<?php

namespace Mmb\Core\Updates\Webhooks;

use Carbon\Carbon;
use Mmb\Core\Data;

/**
 * @property string  $url
 * @property string  $hasCustomCertificate
 * @property int     $pendingUpdateCount
 * @property ?string $ipAddress
 * @property ?Carbon $lastErrorDate
 * @property ?string $lastErrorMessage
 * @property ?Carbon $lastSynchronizationErrorDate
 * @property ?int $maxConnections
 * @property ?string $allowedUpdates
 */
class WebhookInfo extends Data
{

    protected function dataCasts() : array
    {
        return [
            'url'                             => 'string',
            'has_custom_certificate'          => 'bool',
            'pending_update_count'            => 'int',
            'ip_address'                      => 'string',
            'last_error_date'                 => 'date',
            'last_error_message'              => 'string',
            'last_synchronization_error_date' => 'date',
            'max_connections'                 => 'int',
            'allowed_updates'                 => ['string'],
        ];
    }

}