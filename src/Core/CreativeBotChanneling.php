<?php

namespace Mmb\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Creative bot channeling
 *
 * This channeling is default channeling, including dynamic bots saving in the database.
 * You can create many bots in the database.
 */
class CreativeBotChanneling extends DefaultBotChanneling
{

    public string $model;
    public string $nameColumn;
    public ?string $usernameColumn;
    public string $tokenColumn;
    public string $hookColumn;

    public array $databaseArgs;

    public function __construct(array $args)
    {
        parent::__construct($args);

        $this->model = $this->args['database']['model'];
        $this->usernameColumn = $this->args['database']['usernameColumn'] ?? null;
        $this->nameColumn = $this->args['database']['nameColumn'] ?? 'name';
        $this->tokenColumn = $this->args['database']['tokenColumn'] ?? 'token';
        $this->hookColumn = $this->args['database']['hookColumn'] ?? 'hook_token';

        $this->databaseArgs = Arr::except($this->args['database'], ['model', 'usernameColumn', 'nameColumn', 'tokenColumn', 'hookColumn']);

        unset($this->args['database']);
    }

    /**
     * Get bot from configs or database
     *
     * @param string $name
     * @param string|null $hookToken
     * @return Bot
     */
    public function getBot(string $name, ?string $hookToken)
    {
        if (!isset($this->args[$name]['token']) && isset($hookToken)) {
            /** @var Model $info */
            if ($info = $this->model::query()->where($this->hookColumn, $hookToken)->first()) {
                $bot = new Bot(new InternalCreativeBotInfo(
                    token: $info->getAttribute($this->tokenColumn),
                    username: $this->usernameColumn ? $info->getAttribute($this->usernameColumn) : (
                        $this->args[$name]['username'] ?? null
                    ),
                    guardName: $this->args[$name]['guard'] ?? $this->getDefaultGuard(),
                    record: $info,
                ));

                $this->registerBot($bot, ($this->args[$name] ?? []) + $this->databaseArgs);

                return $bot;
            }
        }

        return parent::getBot($name, $hookToken);
    }


    /**
     * Find bot by hook token from configs or database
     *
     * @param string $hookToken
     * @return mixed|string|null
     */
    public function findByHookToken(string $hookToken)
    {
        $result = parent::findByHookToken($hookToken);

        if ($result === null) {
            /** @var Model $info */
            if ($info = $this->model::query()->where($this->hookColumn, $hookToken)->first()) {
                return $info->getAttribute($this->nameColumn);
            }
        }

        return $result;
    }

    public function getWebhookUrl(InternalBotInfo $info)
    {
        if ($info instanceof InternalCreativeBotInfo) {
            return route('mmb.webhook', ['hookToken' => $info->record->getAttribute($this->hookColumn)]);
        }

        return parent::getWebhookUrl($info);
    }

}
