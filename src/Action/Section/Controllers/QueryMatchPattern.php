<?php

namespace Mmb\Action\Section\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Mmb\Action\Action;
use Mmb\Support\Caller\AuthorizationHandleBackException;

class QueryMatchPattern
{

    public function __construct(
        public string $pattern,
    )
    {
    }

    protected $spaces;

    /**
     * Set spaces pattern
     *
     * @param string $regex
     * @return $this
     */
    public function spaces(string $regex)
    {
        $this->spaces = $regex;
        return $this;
    }

    /**
     * Ignore spaces
     *
     * One or more space is valid for any space in your pattern
     *
     * @return $this
     */
    public function ignoreSpaces()
    {
        return $this->spaces('[\s\t\r\n]+');
    }

    /**
     * Optional spaces
     *
     * Zero or more space is valid for any space in your pattern
     *
     * @return $this
     */
    public function optionalSpaces()
    {
        return $this->spaces('[\s\t\r\n]*');
    }


    protected array $patterns = [];

    /**
     * Set custom pattern for a variable
     *
     * @param string $name
     * @param string $regex
     * @return $this
     */
    public function pattern(string $name, string $regex)
    {
        $this->patterns[$name] = $regex;
        return $this;
    }

    /**
     * Set pattern from some values
     *
     * @param string $name
     * @param array  $values
     * @return $this
     */
    public function in(string $name, array $values)
    {
        return $this->pattern(
            $name,
            '(' . implode('|', array_map(fn($value) => preg_quote($value, '/'), $values)) . ')'
        );
    }

    /**
     * Set pattern equals to
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function same(string $name, string $value)
    {
        return $this->pattern($name, preg_quote($value, '/'));
    }

    /**
     * Check variable exists
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return isset($this->getInfo()['vars'][$name]);
    }

    protected $flags = [];

    /**
     * Add regex flag
     *
     * @param string $flag
     * @return $this
     */
    public function flag(string $flag)
    {
        if(strlen($flag) != 1)
        {
            throw new \InvalidArgumentException("Flag [$flag] is not valid");
        }

        if(!in_array($flag, $this->flags))
        {
            $this->flags[] = $flag;
        }

        return $this;
    }

    /**
     * Add regex flags
     *
     * @param string ...$flags
     * @return $this
     */
    public function flags(string ...$flags)
    {
        foreach($flags as $flag)
        {
            foreach(str_split($flag) as $f)
            {
                $this->flag($f);
            }
        }

        return $this;
    }

    /**
     * Enable ignoring cases
     *
     * @return $this
     */
    public function ignoreCase()
    {
        return $this->flag('i');
    }


    protected $json;

    /**
     * Set json variable
     *
     * Json variable give array and set variables
     *
     * @param string $name
     * @return $this
     */
    public function json(string $name)
    {
        if(isset($this->json))
        {
            throw new \InvalidArgumentException("Query pattern already has json variable named [$this->json]");
        }

        $this->json = $name;
        return $this;
    }


    protected $action;

    /**
     * Set action value
     *
     * @param string $value
     * @return $this
     */
    public function action(string $value)
    {
        $this->action = [$value];
        return $this;
    }

    /**
     * Set dynamic action from variable
     *
     * @param string $name
     * @return $this
     */
    public function actionFrom(string $name)
    {
        $this->action = $name;
        return $this;
    }

    /**
     * Get action
     *
     * @return string|null
     */
    public function getAction()
    {
        if($this->action === null)
        {
            return null;
        }
        elseif(is_array($this->action))
        {
            return $this->action[0];
        }
        else
        {
            return $this->matches[$this->action];
        }
    }

    /**
     * Invoke current action
     *
     * @param Action $object
     * @return mixed
     */
    public function invoke(Action $object)
    {
        $action = $this->getAction();

        return $action === null ? null : $object->invokeDynamic($action, [], $this->matches);
    }


    protected $regex;

    /**
     * Get regex pattern
     *
     * @return string
     */
    protected function getAsRegex()
    {
        if(!isset($this->regex))
        {
            $pattern = preg_quote($this->pattern, '/');

            if(isset($this->spaces))
            {
                $pattern = preg_replace('/\s+/', $this->spaces, $pattern);
            }

            $this->regex = preg_replace_callback(
                '/\\\\\{(.+?)(\\\\:(.*?))?\\\\\}/',
                function($match)
                {
                    if(isset($this->patterns[$match[1]]))
                    {
                        $type = $this->patterns[$match[1]];
                    }
                    elseif($match[1] === $this->json)
                    {
                        $type = $this->getPatternType('inline');
                    }
                    else
                    {
                        $type = $this->getPatternType(str_replace('\?', '?', $match[3] ?? ''));
                    }

                    return "(?<$match[1]>$type)";
                },
                $pattern
            );
        }

        return $this->regex;
    }

    protected function getPatternType(?string $name)
    {
        return match ($name ?? 'slug')
        {
            '', 'slug'   => '[^\s\n\r\t]+',
            '?', 'slug?' => '[^\s\n\r\t]*',
            'inline'     => '.+',
            'inline?'    => '.*',
            'any'        => '[\s\S]+',
            'any?'       => '[\s\S]*',
            'int'        => '\d+',
            'int?'       => '\d*',
            'number'     => '\d+\.?\d*',
            'number?'    => '(\d+\.?\d*|)',
            default      => throw new \InvalidArgumentException(
                "Invalid pattern type [$name] in pattern [$this->pattern]",
            ),
        };
    }

    protected $info;

    /**
     * Get info
     *
     * @return array
     */
    protected function getInfo()
    {
        if(isset($this->info))
        {
            return $this->info;
        }

        $vars = [];
        $required = 0;
        preg_match_all('/\{(.+?)(:(.*?))?\}/', $this->pattern, $matches);
        foreach($matches[1] as $i => $name)
        {
            if(isset($this->patterns[$name]))
            {
                $type = $this->patterns[$name];
            }
            elseif($name === $this->json)
            {
                $type = $this->getPatternType('inline');
            }
            else
            {
                $type = $this->getPatternType(@$matches[3][$i]);
            }
            $isOptional = @$matches[3][$i] && str_ends_with($matches[3][$i], '?');
            $vars[$name] = [$type, $isOptional];

            if(!$isOptional)
            {
                $required++;
            }
        }

        return $this->info = ['vars' => $vars, 'required' => $required];
    }

    /**
     * Try to make a query
     *
     * @param array $args
     * @return string|false
     */
    public function make(array $args)
    {
        $info = $this->getInfo();
        $argsCount = count($args);

        if(isset($this->json))
        {
            if($argsCount < $info['required'] - 1)
            {
                return false;
            }
        }
        else
        {
            if($argsCount < $info['required'])
            {
                return false;
            }

            if($argsCount > count($info['vars']))
            {
                return false;
            }
        }

        $optionals = $argsCount - $info['required'];
        $replaces = [];
        $i = 0;
        foreach($info['vars'] as $name => $var)
        {
            [$regex, $isOptional] = $var;

            if(array_key_exists($name, $args))
            {
                $value = $args[$name];
                unset($args[$name]);
            }
            elseif($isOptional)
            {
                if($optionals > 0 && array_key_exists($i, $args))
                {
                    $value = $args[$i];
                    unset($args[$i]);
                    $optionals--;
                    $i++;
                }
                else
                {
                    $value = null;
                }
            }
            elseif($name === $this->json)
            {
                continue;
            }
            else
            {
                if(!array_key_exists($i, $args))
                {
                    return false;
                }

                $value = $args[$i];
                unset($args[$i++]);
            }

            if($value !== null)
            {
                if(!preg_match('/^' . $regex . '$/', $value))
                {
                    return false;
                }
            }
            elseif(!$isOptional)
            {
                return false;
            }

            $replaces[$name] = $value;
        }

        if(isset($this->json) && !array_key_exists($this->json, $replaces))
        {
            $replaces[$this->json] = json_encode($args);
        }
        elseif(count($args))
        {
            return false;
        }

        return preg_replace_callback('/\{(.+?)(:.*?)?\}/', fn($match) => $replaces[$match[1]], $this->pattern);
    }

    /**
     * Result matches variants
     *
     * @var array
     */
    protected array $matches;

    /**
     * Match pattern
     *
     * @param string $value
     * @return bool
     */
    public function match(string $value)
    {
        if(preg_match(
            '/^' . $this->getAsRegex() . '$/' . implode($this->flags),
            $value,
            $matches,
        ))
        {
            $this->matches = [];
            foreach($matches as $name => $match)
            {
                if(!is_int($name))
                {
                    $match = $match === '' ? null : $match;

                    if($name === $this->json)
                    {
                        $match = @json_decode($match, true);
                        if(!is_array($match))
                        {
                            return false;
                        }

                        $this->matches = array_replace($this->matches, $match);
                        continue;
                    }

                    $this->matches[$name] = $match;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Get all matches variants
     *
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Get match variant
     *
     * @param string $name
     * @return string
     */
    public function getMatch(string $name)
    {
        return $this->matches[$name];
    }

}
