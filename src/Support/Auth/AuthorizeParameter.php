<?php

namespace Mmb\Support\Auth;

use Attribute;
use Illuminate\Contracts\Auth\Guard;
use Mmb\Support\Caller\CallingParameterAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class AuthorizeParameter extends CallingParameterAttribute
{

    public function __construct(
        public $ability
    )
    {
    }

    public function authorize($value)
    {
        app(Guard::class)->forUser(auth()->user())->authorize($this->ability, $value);
    }

}
