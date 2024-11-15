<?php

namespace Mmb\Core\Updates\Inlines;

use Mmb\Core\Data;
use Mmb\Core\Updates\Data\Location;
use Mmb\Core\Updates\Infos\UserInfo;

/**
 * @property string    $id       Unique identifier for this query
 * @property UserInfo  $from     Sender
 * @property string    $query    Text of the query (up to 256 characters)
 * @property string    $offset   Offset of the results to be returned, can be controlled by the bot
 * @property ?string   $chatType Optional. Type of the chat from which the inline query was sent.
 * @property ?Location $location Optional. Sender location, only for bots that request user location
 */
class InlineQuery extends Data
{

    protected function dataCasts() : array
    {
        return [
            'id'        => 'string',
            'from'      => UserInfo::class,
            'query'     => 'string',
            'offset'    => 'string',
            'chat_type' => 'string',
            'location'  => Location::class,
        ];
    }

    public function answer($results = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'inlineQueryId' => $this->id,
                'results'       => $results,
            ],
            $args + $namedArgs
        );

        return $this->bot()->answerCallback($args);
    }

}