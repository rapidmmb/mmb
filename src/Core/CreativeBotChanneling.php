<?php

namespace Mmb\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Mmb\Support\Db\ModelFinder;

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
     * @return Bot
     */
    public function getBot(string $name)
    {
        if(!isset($this->args[$name]))
        {
            /** @var Model $info */
            if($info = ModelFinder::findBy($this->model, $name, $this->nameColumn))
            {
                $bot = new Bot(
                    $info->getAttribute($this->tokenColumn),
                    $this->usernameColumn ? $info->getAttribute($this->usernameColumn) : null
                );

                $this->registerBot($bot, $this->databaseArgs);

                return $bot;
            }
        }

        return parent::getBot($name);
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

        if($result === null)
        {
            /** @var Model $info */
            if($info = ModelFinder::findBy($this->model, $hookToken, $this->hookColumn))
            {
                return $info->getAttribute($this->nameColumn);
            }
        }

        return $result;
    }

}
