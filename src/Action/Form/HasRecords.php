<?php

namespace Mmb\Action\Form;

use Illuminate\Database\Eloquent\Model;
use Mmb\Support\Specials\ValueChanges;

trait HasRecords
{

    /**
     * Get the record
     *
     * @return Model
     */
    protected abstract function record() : Model;


    protected $__record;

    /**
     * Get current record
     *
     * @return Model
     */
    public final function getRecord() : Model
    {
        return $this->__record ??= $this->record();
    }

    /**
     * Update the record
     *
     * @param array $inputs
     * @param array $extra
     * @return bool
     */
    public function updateRecord(array $inputs, array $extra = []) : bool
    {
        $attributes = $extra;

        foreach ($inputs as $key => $value)
        {
            if (is_int($key))
            {
                $attributes[$value] = $this->get($value, ValueChanges::NoChanges);
            }
            elseif (is_array($value))
            {
                if (ValueChanges::NoChanges != $val = $this->get($key, ValueChanges::NoChanges))
                {
                    foreach ($value as $key2 => $value2)
                    {
                        $attributes[$key2] = $value2($val);
                    }
                }
            }
            elseif (is_string($value))
            {
                $attributes[$value] = $this->get($key, ValueChanges::NoChanges);
            }
            elseif ($value instanceof \Closure)
            {
                if (ValueChanges::NoChanges != $val = $this->get($key, ValueChanges::NoChanges))
                {
                    $attributes[$key] = $value($val);
                }
            }
            else
            {
                throw new \TypeError(
                    sprintf("Expected array or closure, given [%s] in key [%s]", smartTypeOf($value), $key)
                );
            }
        }

        $attributes = array_filter($attributes, fn ($value) => $value !== ValueChanges::NoChanges);

        return $this->getRecord()->update($attributes);
    }

}
