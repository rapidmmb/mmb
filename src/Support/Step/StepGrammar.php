<?php

namespace Mmb\Support\Step;

use Mmb\Action\Memory\StepHandler;
use Mmb\Action\Memory\StepMemory;

class StepGrammar
{
    public static function stringToStep(string $step): ?StepHandler
    {
        if (!$step) {
            return null;
        }

        if (!is_array($data = @json_decode($step, true))) {
            return null;
        }

        if (
            !array_key_exists('_', $data) ||
            !class_exists($data['_']) ||
            !is_a($data['_'], StepHandler::class, true)
        ) {
            return null;
        }

        // Try to create step handler
        try {
            $memory = StepMemory::make(is_array(@$data['m']) ? $data['m'] : []);
            return $data['_']::make($memory);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function stepToString(StepHandler $step): ?string
    {
        $step->save($memory = StepMemory::make());

        return json_encode([
            '_' => get_class($step),
            'm' => $memory->toArray(),
        ]);
    }
}