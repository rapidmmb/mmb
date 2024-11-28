<?php

namespace Mmb\Action\Filter\Concerns;

use Illuminate\Support\Collection;

trait AdvancedRule
{

    protected array $shouldRules = [[]];

    /**
     * Add a new rule
     *
     * @param string $attribute
     * @param string $operator
     * @param        $value
     * @param mixed  $error
     * @return $this
     */
    public function should(string $attribute, string $operator, $value, mixed $error = null)
    {
        $this->shouldRules[array_key_last($this->shouldRules)][] = [$attribute, $operator, $value, $error];
        return $this;
    }

    /**
     * Add an "or" rule
     *
     * @return $this
     */
    public function or()
    {
        $this->shouldRules[] = [];
        return $this;
    }

    /**
     * Add an "or" and new rule
     *
     * @param string $attribute
     * @param string $operator
     * @param        $value
     * @param mixed  $error
     * @return $this
     */
    public function orShould(string $attribute, string $operator, $value, mixed $error = null)
    {
        return $this->or()->should(...func_get_args());
    }

    /**
     * Add a new rule
     *
     * @param string $attribute
     * @param string $operator
     * @param        $value
     * @param mixed  $error
     * @return $this
     */
    public function andShould(string $attribute, string $operator, $value, mixed $error = null)
    {
        return $this->should(...func_get_args());
    }

    /**
     * Pass rules
     *
     * @param $originalValue
     * @return void
     */
    protected function passAdvanced($originalValue)
    {
        foreach ($this->shouldRules as [$attribute, $operator, $right, $error])
        {
            $value = $originalValue;
            if ($attribute != '')
            {
                foreach (explode('.', $attribute) as $segment)
                {
                    $value = $value?->$segment;
                }
            }

            $result = match (strtolower($operator))
            {
                '=', '=='       => $value == $right,
                'is', '==='     => $value === $right,
                'is not', '!==' => $value !== $right,
                '>'             => $value > $right,
                '<'             => $value < $right,
                '>='            => $value >= $right,
                '<='            => $value <= $right,
                '!=', '<>'      => $value != $right,
                'in'            => $right instanceof Collection ?
                    $right->search($value) :
                    in_array($value, $right),
                'in strict'     => $right instanceof Collection ?
                    $right->search($value, true) :
                    in_array($value, $right, true),
                'match'         => preg_match($right, $value),
                'contains'      => str_contains($value, $right),
                '()', 'invoke'  => $right($value),
                default         => throw new \InvalidArgumentException("Operator [$operator] is not valid"),
            };

            if (!$result)
            {
                $this->fail($error);
            }
        }
    }

}