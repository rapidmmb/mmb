<?php

namespace Mmb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mmb\Core\Bot;
use Mmb\Core\BotChanneling;
use Mmb\Core\Updates\Update;

class WebhookController extends Controller
{

    public function update(string $hookToken, Request $request)
    {
        return app(BotChanneling::class)->onRoute($hookToken, $request);
    }

}
