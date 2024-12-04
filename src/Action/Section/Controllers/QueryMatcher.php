<?php

namespace Mmb\Action\Section\Controllers;

use Illuminate\Support\Facades\Hash;
use Mmb\Action\Action;
use Mmb\Action\Section\Attributes\FixedDialog;

class QueryMatcher
{

    public function __construct(
        public ?string $type = null,
    )
    {
    }

    /**
     * Make new instance
     *
     * @param string|null $type
     * @return QueryMatcher
     */
    public static function make(string $type = null)
    {
        return new QueryMatcher($type);
    }

    /**
     * Make new instance and initialize it
     *
     * @param string|null $type
     * @param Action $object
     * @param string $method
     * @return QueryMatcher
     */
    public static function makeFrom(?string $type, Action $object, string $method)
    {
        $matcher = static::make($type);
        $object->$method($matcher);

        return $matcher;
    }

    /**
     * @var QueryMatchPattern[]
     */
    protected array $matches = [];

    /**
     * Add pattern
     *
     * - Blank pattern: `"empty"`
     * - Value pattern: `"/start {id}"`
     * - Filtered value pattern: `"/say {message:any}"`
     * - Optional value: `"set{method:slug?} to {value:any}"`
     *
     * Value types:
     * - `slug`: anything except spaces (default)
     * - `any`: anything
     * - `inline`: anything except newline
     * - `int`: integer number
     * - `number`: integer or float number
     *
     * @param string $pattern
     * @param string|null $action
     * @param string|null $actionFrom
     * @return QueryMatchPattern
     */
    public function match(string $pattern, string $action = null, string $actionFrom = null)
    {
        $p = new QueryMatchPattern($pattern);

        if (isset($action))
            $p->action($action);
        if (isset($actionFrom))
            $p->actionFrom($actionFrom);

        $this->matches[] = $p;
        return $p;
    }

    /**
     * Get class id for a class
     *
     * @param string $class
     * @return mixed
     */
    public static function getClassId(string $class)
    {
        return crc32($class);
    }

    /**
     * Auto match from class attributes
     *
     * @param Action $object
     * @return void
     */
    public function autoMatch(Action $object)
    {
        $base = match ($this->type) {
            'callback' => Attributes\OnCallback::class,
            'inline' => Attributes\OnInline::class,
            default => throw new \InvalidArgumentException(
                "Query matcher of type [$this->type] is not supported auto matching"
            ),
        };
        $prefix = static::getClassId(get_class($object)) . ':';

        $methods = [];
        foreach (get_class_methods($object) as $method) {
            if ($attr = $object::getMethodAttributeOf($method, $base)) {

                if ($attr instanceof FixedDialog) {

                    $pattern = $this->match($attr->full ? $attr->pattern : $prefix . $attr->pattern);
                    $pattern->dontMake();
                    $pattern->action(fn() => $attr->fire($object->context, $pattern, get_class($object), $method));

                } else {

                    if (isset($attr->pattern)) {
                        $pattern = $this->match($attr->full ? $attr->pattern : $prefix . $attr->pattern);
                        if ($pattern->has('_')) {
                            $pattern->same('_', $attr->name ?? $method);
                        }
                        $pattern->action($method);
                    } else {
                        $methods[] = $method;
                    }

                }

            }
        }

        if ($methods) {
            $this->match($prefix . '{_method}:{_args}')
                ->in('_method', $methods)
                ->json('_args')
                ->actionFrom('_method');
        }
    }

    /**
     * Find pattern
     *
     * @param string $value
     * @return QueryMatchPattern|null
     */
    public function findPattern(string $value)
    {
        foreach ($this->matches as $match) {
            if ($match->match($value)) {
                return $match;
            }
        }

        return null;
    }

    /**
     * Make query
     *
     * @param ...$args
     * @return string
     */
    public function makeQuery(...$args)
    {
        foreach ($this->matches as $match) {
            if (($query = $match->make($args)) !== false) {
                return $query;
            }
        }

        throw new \InvalidArgumentException("No pattern found");
    }

}
