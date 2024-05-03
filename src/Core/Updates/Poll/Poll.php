<?php

namespace Mmb\Core\Updates\Poll;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mmb\Core\Data;

/**
 * @property string                 $id
 * @property string                 $question
 * @property Collection<PollOption> $options
 * @property int                    $totalVoterCount
 * @property bool                   $isClosed
 * @property bool                   $isAnonymous
 * @property string                 $type
 * @property bool                   $allowMultipleAnswers
 * @property ?int                   $correctOptionId
 * @property ?string                $explanation
 * @property ?EntityCollection      $explanationEntities
 * @property ?int                   $openPeriod
 * @property ?Carbon                $closeDate
 *
 * @property PollOption             $correctOption
 */
class Poll extends Data
{

    protected function dataCasts() : array
    {
        return [
            'id'                      => 'int',
            'question'                => 'string',
            'options'                 => [PollOption::class],
            'total_voter_count'       => 'int',
            'is_closed'               => 'bool',
            'is_anonymous'            => 'bool',
            'type'                    => 'string',
            'allows_multiple_answers' => 'bool',
            'correct_option_id'       => 'int',
            'explanation'             => 'string',
            // 'explanation_entities' => EntityCollection::class,
            'open_period'             => 'int',
            'close_date'              => 'date',
        ];
    }

    // Not trusted
    protected function getCorrectOptionAttribute()
    {
        return $this->options->firstWhere('id', $this->correctOptionId);
    }

}