<?php

namespace Mmb\Core\Client\Parser\Keyboard;

use Illuminate\Support\Arr;
use Mmb\Core\Client\Parser\ArrayParser;

class ReplyKeyboardMarkupArrayParser extends ArrayParser
{

    public function __construct()
    {
        parent::__construct(
            [
                'keyboard'              => $this->parseAutoKeyboard(...),
                'key'                   => $this->parseAutoKeyboard(...),
                'normalKeyboard'        => $this->parseKeyboard(...),
                'normal'                => $this->parseKeyboard(...),
                'isPersistent'          => 'isPersistent',
                'resizeKeyboard'        => 'resizeKeyboard',
                'resize'                => 'resizeKeyboard',
                'oneTimeKeyboard'       => 'oneTimeKeyboard',
                'oneTime'               => 'oneTimeKeyboard',
                'once'                  => 'oneTimeKeyboard',
                'inputFieldPlaceholder' => 'inputFieldPlaceholder',
                'inputPlaceholder'      => 'inputFieldPlaceholder',
                'placeholder'           => 'inputFieldPlaceholder',
                'selective'             => 'selective',
                'removeKeyboard'        => 'removeKeyboard',
                'remove'                => 'removeKeyboard',
                'inlineKeyboard'        => $this->parseInlineKeyboard(...),
                'inline'                => $this->parseInlineKeyboard(...),
            ],
            errorOnFail: true,
        );
    }

    public function parseKeyboard($key, $value, $real)
    {
        return [
            'keyboard' => $this->convertKeyboard($key, $value),
        ];
    }

    public function parseInlineKeyboard($key, $value, $real)
    {
        return [
            'inline_keyboard' => $this->convertKeyboard($key, $value),
        ];
    }

    public function parseAutoKeyboard($key, $value, $real)
    {
        $key = $this->convertKeyboard($key, $value);

        $isInline = false;
        foreach($key as $row)
        {
            foreach($row as $column)
            {
                if(Arr::hasAny(
                    $column,
                    ['callback_data', 'url', 'switch_inline_query', 'switch_inline_query_current_chat', 'switch_inline_query_chosen_chat']
                ))
                {
                    $isInline = true;
                    break 2;
                }
                elseif(Arr::hasAny(
                    $column, ['request_user', 'request_chat', 'request_poll', 'request_contact', 'request_location']
                ))
                {
                    // $isInline = false;
                    break 2;
                }
            }
        }

        return [
            $isInline ? 'inline_keyboard' : 'keyboard' => $key,
        ];
    }

    public function convertKeyboard($key, $value)
    {
        if(!is_array($value))
        {
            throw new \TypeError("Argument [$key] should be array, given " . gettype($value));
        }

        $keyboard = [];
        foreach($value as $rowKey => $row)
        {
            if(is_null($row))
            {
                continue;
            }

            if(!is_array($row))
            {
                throw new \TypeError(
                    sprintf("Argument [%s] has valid values of type %s at index [%s]", $key, gettype($row), $rowKey)
                );
            }

            $keyboardRow = [];
            foreach($row as $colKey => $col)
            {
                if(is_null($col))
                {
                    continue;
                }

                if(!is_array($col))
                {
                    throw new \TypeError(
                        sprintf(
                            "Argument [%s] has valid values of type %s at index [%s][%s]", $key, gettype($row), $rowKey,
                            $colKey
                        )
                    );
                }

                if($keyboardCol = app(KeyboardArrayParser::class)->normalize($col))
                {
                    $keyboardRow[] = $keyboardCol;
                }
            }

            if($keyboardRow)
            {
                $keyboard[] = $keyboardRow;
            }
        }

        return $keyboard;
    }


    public function normalize(array $values) : array
    {
        if(array_is_list($values))
        {
            $values = ['key' => $values, 'resize' => true];
        }

        return parent::normalize($values);
    }

}
