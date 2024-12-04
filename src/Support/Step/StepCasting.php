<?php

namespace Mmb\Support\Step;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Memory\StepHandler;
use Mmb\Action\Memory\StepMemory;

class StepCasting implements Castable
{

    public static function castUsing(array $arguments)
    {
        return new class() implements CastsAttributes {
            public function get(Model $model, string $key, mixed $value, array $attributes)
            {
                // Check null value
                if (!$value || !is_array($data = Json::decode($value))) {
                    return null;
                }

                // Check null or crashed step
                if (!@$data['_'] || !class_exists($data['_'])) {
                    return null;
                }

                // Check the class is instance of StepHandler
                if (!is_a($data['_'], StepHandler::class, true)) {
                    return null;
                }

                // Try to create step handler
                try {
                    $memory = StepMemory::make(is_array(@$data['m']) ? $data['m'] : []);
                    return $data['_']::make($memory);
                } catch (\Throwable $exception) {
                    return null;
                }
            }

            public function set(Model $model, string $key, mixed $value, array $attributes)
            {
                if ($value instanceof StepHandler) {
                    $value->save($memory = StepMemory::make());
                    $step = [
                        '_' => get_class($value),
                        'm' => $memory->toArray(),
                    ];
                } else {
                    $step = [
                        '_' => null,
                    ];
                }

                return [$key => Json::encode($step)];
            }
        };
    }
}
