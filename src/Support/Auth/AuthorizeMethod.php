<?php

namespace Mmb\Support\Auth;

use Attribute;
use Illuminate\Contracts\Auth\Guard;
use Mmb\Support\Caller\CallingMethodAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AuthorizeMethod extends CallingMethodAttribute
{

    public function __construct(
        public $ability
    )
    {
    }

    public function authorize()
    {
        app(Guard::class)->forUser(auth()->user())->authorize($this->ability);
    }

    public function can()
    {
        return app(Guard::class)->forUser(auth()->user())->can($this->ability);
    }

}
