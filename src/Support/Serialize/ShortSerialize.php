<?php

namespace Mmb\Support\Serialize;

use BackedEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Mmb\Core\Updates\Callbacks\Callback;
use Mmb\Core\Updates\Data\Contact;
use Mmb\Core\Updates\Data\Dice;
use Mmb\Core\Updates\Data\Location;
use Mmb\Core\Updates\Data\Venue;
use Mmb\Core\Updates\Files\Animation;
use Mmb\Core\Updates\Files\Audio;
use Mmb\Core\Updates\Files\Document;
use Mmb\Core\Updates\Files\FileInfo;
use Mmb\Core\Updates\Files\Photo;
use Mmb\Core\Updates\Files\PhotoCollection;
use Mmb\Core\Updates\Files\Video;
use Mmb\Core\Updates\Files\VideoNote;
use Mmb\Core\Updates\Files\Voice;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\ChatMember;
use Mmb\Core\Updates\Infos\ChatShared;
use Mmb\Core\Updates\Infos\UserInfo;
use Mmb\Core\Updates\Infos\UserProfilePhotos;
use Mmb\Core\Updates\Infos\UserShared;
use Mmb\Core\Updates\Messages\InlineMessage;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Messages\MessageBuilder;
use Mmb\Core\Updates\Poll\Poll;
use Mmb\Core\Updates\Poll\PollAnswer;
use Mmb\Core\Updates\Poll\PollOption;
use Mmb\Core\Updates\Update;
use Mmb\Core\Updates\Webhooks\WebhookInfo;

class ShortSerialize
{

    /**
     * Class aliases
     *
     * @var string[]
     */
    public static $aliases = [
        'c' => Carbon::class,
        'C' => \Illuminate\Support\Carbon::class,

        'uu' => Update::class,
        'um' => Message::class,
        'uim' => InlineMessage::class,
        'umb' => MessageBuilder::class,
        'uc' => Callback::class,
        'dc' => Contact::class,
        'dd' => Dice::class,
        'dl' => Location::class,
        'dv' => Venue::class,
        'da' => Animation::class,
        'du' => Audio::class,
        'df' => FileInfo::class,
        'dD' => Document::class,
        'dp' => Photo::class,
        'dP' => PhotoCollection::class,
        'dV' => Video::class,
        'do' => Voice::class,
        'dn' => VideoNote::class,
        'ci' => ChatInfo::class,
        'cm' => ChatMember::class,
        'cs' => ChatShared::class,
        'ui' => UserInfo::class,
        'up' => UserProfilePhotos::class,
        'us' => UserShared::class,
        'dpl' => Poll::class,
        'dpa' => PollAnswer::class,
        'dpo' => PollOption::class,
        'wi' => WebhookInfo::class,
    ];

    /**
     * Serialize value
     *
     * @param $object
     * @return string
     */
    public static function serialize($object)
    {
        return static::serializeAny($object, new \WeakMap);
    }

    /**
     * Serialize an object
     *
     * @param          $object
     * @param \WeakMap $stack
     * @return string
     */
    protected static function serializeAny($object, \WeakMap $stack)
    {
        switch (gettype($object))
        {
            case 'integer':
                return 'i:' . $object . ';';

            case 'double':
                return 'd:' . $object . ';';

            case 'boolean':
                return $object ? 'T;' : 'F;';

            case 'string':
                return 's:' . strlen($object) . ':' . $object;

            case 'array':
                return static::serializeArray($object, $stack);

            case 'object':
                if ($object instanceof BackedEnum)
                {
                    return static::serializeObject($object, $stack);
                }

                if ($stack->offsetExists($object))
                {
                    return 'r:' . $stack->offsetGet($object) . ';';
                }

                $stack->offsetSet($object, $stack->count());
                return static::serializeObject($object, $stack);

            default:
                return 'N;';
        }
    }

    /**
     * Serialize a class value
     *
     * @param object   $object
     * @param \WeakMap $stack
     * @return string
     */
    protected static function serializeObject(object $object, \WeakMap $stack)
    {
        if ($object instanceof Model)
        {
            $string = serialize($object);
            return 'p:' . strlen($string) . ':' . $string;
        }

        $class = get_class($object);
        $type = array_search($class, static::$aliases) ?: $class;

        if ($object instanceof BackedEnum)
        {
            return "e:$type:" . static::serializeAny($object->value, $stack);
        }

        if ($object instanceof Shortable)
        {
            $data = $object->shortSerialize();
        }
        elseif ($object instanceof \Carbon\Carbon)
        {
            $data = ['t' => $object->timestamp];
        }
        else
        {
            $data = [];
            static::exportObjectData(
                $object,
                new \ReflectionClass($object),
                $data,
                $object instanceof ShortableProperties ? $object->getShortProperties() : null,
                $object instanceof ShortableBlacklistProperties ? $object->getShortBlacklistProperties() : null,
                $object instanceof ShortableAliases ? $object->getShortAliases() : null,
            );
        }

        $r = '';
        foreach ($data as $key => $value)
        {
            $r .= $key . '=';
            $r .= static::serializeAny($value, $stack);
        }

        $count = count($data);
        return "$type:$count:$r";
    }

    /**
     * Export object properties
     *
     * @param object           $object
     * @param \ReflectionClass $class
     * @param array            $data
     * @return void
     */
    protected static function exportObjectData(object $object, \ReflectionClass $class, array &$data, ?array $whitelist, ?array $blacklist, ?array $aliases)
    {
        foreach ($class->getProperties() as $property)
        {
            if ($property->isInitialized($object))
            {
                if ($whitelist && !in_array($property->name, $whitelist) && !in_array($class->name . '.' . $property->name, $whitelist))
                {
                    continue;
                }

                if ($blacklist && (in_array($property->name, $blacklist) || in_array($class->name . '.' . $property->name, $blacklist)))
                {
                    continue;
                }

                if ($aliases && (($key = array_search($class->name . '.' . $property->name, $aliases)) !== false || ($key = array_search($property->name, $aliases)) !== false))
                {
                    // Alias applied
                }
                elseif ($property->isPrivate())
                {
                    $key = array_search($class->name, static::$aliases) ?: $class->name;
                    $key .= '.' . $property->name;
                }
                else
                {
                    $key = $property->name;
                }

                if (!array_key_exists($key, $data))
                {
                    $data[$key] = $property->getValue($object);
                }
            }
        }

        if ($parent = $class->getParentClass())
        {
            static::exportObjectData($object, $parent, $data, $whitelist, $blacklist, $aliases);
        }
    }

    /**
     * Serialize an array
     *
     * @param array    $array
     * @param \WeakMap $stack
     * @return string
     */
    protected static function serializeArray(array $array, \WeakMap $stack)
    {
        if (array_is_list($array))
        {
            $r = '';
            foreach ($array as $value)
            {
                $r .= static::serializeAny($value, $stack);
            }

            $count = count($array);
            return "l:$count:$r";
        }
        else
        {
            $i = 0;
            $r = '';
            foreach ($array as $key => $value)
            {
                if ($i === $key)
                {
                    $i++;
                    $r .= '+' . static::serializeAny($value, $stack);
                }
                elseif (is_int($key))
                {
                    $r .= '#' . $key . '>' . static::serializeAny($value, $stack);
                }
                else
                {
                    $r .= '&' . strlen($key) . '>' . $key . static::serializeAny($value, $stack);
                }
            }
            $count = count($array);
            return "a:$count:$r";
        }
    }


    public static function tryUnserialize(string $value)
    {
        try
        {
            return static::unserialize($value);
        }
        catch (\Exception $e)
        {
            return null;
        }
    }

    /**
     * Un-serialize object
     *
     * @param string $value
     * @return mixed
     */
    public static function unserialize(string $value)
    {
        $ptr = 0;
        $stack = [];
        return static::fetchAny($value, $ptr, $stack);
    }

    /**
     * Fetch next object
     *
     * @param string $value
     * @param int    $ptr
     * @param array  $stack
     * @return mixed
     */
    protected static function fetchAny(string $value, int &$ptr, array &$stack)
    {
        // Fixed values
        if (@$value[$ptr + 1] == ';')
        {
            switch (@$value[$ptr])
            {
                case 'T':
                    $ptr += 2;
                    return true;

                case 'F':
                    $ptr += 2;
                    return false;

                case 'N':
                    $ptr += 2;
                    return null;
            }
        }

        // Check type
        $type = static::fetch($value, $ptr, ':');

        switch ($type)
        {
            // Numeric value
            case 'i':
            case 'd':
                return static::fetchNumber($value, $ptr);

            // Reference value
            case 'r':
                return @$stack[static::fetchNumber($value, $ptr)];

            // String value
            case 's':
                return static::fetchString($value, $ptr);

            // List value
            case 'l':
                $count = static::fetchNumber($value, $ptr, ':');

                $list = [];
                for ($i = 0; $i < $count; $i++)
                {
                    $list[] = static::fetchAny($value, $ptr, $stack);
                }

                return $list;

            // Array value
            case 'a':
                $count = static::fetchNumber($value, $ptr, ':');

                $list = [];
                for ($i = 0; $i < $count; $i++)
                {
                    switch (@$value[$ptr++])
                    {
                        case '+':
                            $list[] = static::fetchAny($value, $ptr, $stack);
                            break;

                        case '#':
                            $list[static::fetchNumber($value, $ptr, '>')]
                                = static::fetchAny($value, $ptr, $stack);
                            break;

                        case '&':
                            $list[static::fetchString($value, $ptr, '>')]
                                = static::fetchAny($value, $ptr, $stack);
                            break;
                    }
                }

                return $list;

            // Normal serialized-object
            case 'p':
                return @unserialize(static::fetchString($value, $ptr));

            // Backed enum object
            case 'e':
                $type = static::fetch($value, $ptr, ':');
                $class = static::$aliases[$type] ?? $type;

                if (!class_exists($class) || !is_a($class, BackedEnum::class, true))
                {
                    return null;
                }

                return $class::tryFrom(
                    static::fetchAny($value, $ptr, $stack)
                );


            // Object un-serialization
            default:
                $class = static::$aliases[$type] ?? $type;
                if (!class_exists($class))
                {
                    return null;
                }

                if (is_a($class, Carbon::class, true))
                {
                    $data = static::fetchData($value, $ptr, $stack);
                    $carbon = new $class($data['t'] ?? null);
                    $stack[] = $carbon;

                    return $carbon;
                }

                $ref = new \ReflectionClass($class);
                $object = $ref->newInstanceWithoutConstructor();
                $stack[] = $object;

                $data = static::fetchData($value, $ptr, $stack);

                if ($object instanceof Shortable)
                {
                    $object->shortUnserialize($data);
                }
                else
                {
                    $aliases = $object instanceof ShortableAliases ? $object->getShortAliases() : null;

                    foreach ($data as $key => $val)
                    {
                        try
                        {
                            if ($aliases && array_key_exists($key, $aliases))
                            {
                                $key = $aliases[$key];
                            }

                            if (str_contains($key, '.'))
                            {
                                try
                                {
                                    [$target, $key] = explode('.', $key, 2);
                                    $target = static::$aliases[$target] ?? $target;
                                    $property = new \ReflectionProperty($target, $key);
                                }
                                catch (\ReflectionException $e)
                                {
                                    $property = $ref->getProperty($key);
                                }
                            }
                            else
                            {
                                $property = $ref->getProperty($key);
                            }

                            $property->setAccessible(true);
                            $property->setValue($object, $val);
                        }
                        catch (\ReflectionException $e)
                        {
                            $object->{$key} = $val;
                        }
                    }
                }

                return $object;
        }
    }

    /**
     * Fetch next value
     *
     * @param string $value
     * @param int    $ptr
     * @param string $breaker
     * @return string
     */
    protected static function fetch(string $value, int &$ptr, string $breaker)
    {
        $text = '';
        for (; $ptr < strlen($value); $ptr++)
        {
            if ($value[$ptr] == $breaker) break;
            $text .= $value[$ptr];
        }
        $ptr++;

        return $text;
    }

    /**
     * Fetch next number
     *
     * @param string $value
     * @param int    $ptr
     * @param string $breaker
     * @return int
     */
    protected static function fetchNumber(string $value, int &$ptr, string $breaker = ';')
    {
        return @+static::fetch($value, $ptr, $breaker);
    }

    /**
     * Fetch next string
     *
     * @param string $value
     * @param int    $ptr
     * @param string $sep
     * @return string
     */
    protected static function fetchString(string $value, int &$ptr, string $sep = ':')
    {
        $length = static::fetchNumber($value, $ptr, $sep);
        $result = substr($value, $ptr, $length);
        $ptr += $length;

        return $result;
    }

    /**
     * Fetch object data
     *
     * @param string $value
     * @param int    $ptr
     * @return array
     */
    protected static function fetchData(string $value, int &$ptr, array &$stack) : array
    {
        $count = static::fetchNumber($value, $ptr, ':');

        $data = [];
        for ($i = 0; $i < $count; $i++)
        {
            $data[static::fetch($value, $ptr, '=')]
                = static::fetchAny($value, $ptr, $stack);
        }

        return $data;
    }

}
