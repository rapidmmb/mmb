<?php

namespace Mmb\Core;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Mmb\Core\Requests\HasRequest;
use Mmb\Core\Updates\Update;
use Mmb\Support\Exceptions\CallableException;

class Bot
{
    use HasRequest,
        Macroable,
        Traits\ApiBotInfos,
        Traits\ApiBotMessages,
        Traits\ApiBotUpdates,
        Traits\ApiBotCallbacks,
        Traits\ApiBotChats,
        Traits\ApiBotUsers,
        Traits\ApiBotFiles;

    public function __construct(
        private string $token,
        public ?string $username = null,
    )
    {
    }

    public function getUpdate()
    {
        if($update = request()->json())
        {
            return Update::make($update, $this, true);
        }
        else
        {
            return false;
        }
    }

    /**
     * Create data
     *
     * @template T
     * @param class-string<T> $class
     * @param                 $data
     * @param bool            $trustedData
     * @return ?T
     */
    public function makeData(string $class, $data, bool $trustedData = true)
    {
        if($data === null || $data === false)
        {
            return null;
        }

        return $class::make($data, $this, $trustedData);
    }

    /**
     * Create data collection
     *
     * @template T
     * @param class-string<T> $class
     * @param                 $data
     * @param bool            $trustedData
     * @return Collection<T>
     */
    public function makeDataCollection(string $class, $data, bool $trustedData = true)
    {
        if(!is_array($data))
        {
            return collect();
        }

        return collect($data)->map(fn($item) => $class::make($item, $this, $trustedData));
    }

    /**
     * Update handlers
     *
     * @var array|null
     */
    public ?array $updateHandlers = null;

    /**
     * Register update handlers
     *
     * @param array $handlers
     * @return void
     */
    public function registerHandlers(array $handlers)
    {
        $this->updateHandlers = $handlers;
    }

    public function handleUpdate(Update $update)
    {
        try
        {
            $request = request();
            // $request->merge(['update' => $update]);
            Container::getInstance()->instance(Update::class, $update);

            if(!isset($this->updateHandlers))
            {
                throw new Exception("Handlers is not set");
            }

            foreach($this->updateHandlers as $updateHandler)
            {
                $updateHandler = new $updateHandler($update);

                if($updateHandler->condition())
                {
                    $updateHandler->handle();
                    break;
                }
            }
        }
        catch(\Throwable $e)
        {
            if($e instanceof HttpResponseException)
            {
                ; // TODO
            }

            if($e instanceof CallableException)
            {
                $e->invoke($update);
                return;
            }

            throw $e;
            app(ExceptionHandler::class)->report($e);
            app(ExceptionHandler::class)->render(request(), $e);
        }
    }


    protected function mergeMultiple(array $valueArgs, array $fixedArgs)
    {
        $args = [];
        foreach($valueArgs as $key => $value)
        {
            if(is_array($value))
            {
                $args = array_replace($args, $value);
            }
            elseif($value !== null)
            {
                $args[$key] = $value;
            }
        }

        return $fixedArgs + $args;
    }

}
