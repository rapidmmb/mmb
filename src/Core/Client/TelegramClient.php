<?php

namespace Mmb\Core\Client;

use Amp\ByteStream\ReadableResourceStream;
use Amp\Http\Client\Connection\DefaultConnectionFactory;
use Amp\Http\Client\Connection\UnlimitedConnectionPool;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request as AmpRequest;
use Closure;
use GuzzleHttp\Psr7\MultipartStream;
use Mmb\Core\Client\Exceptions\TelegramResponseException;
use Mmb\Core\Client\Exceptions\TelegramException;
use Mmb\Core\Client\Extensions\Http1TunnelConnector;
use Mmb\Core\Client\Query\UploadContents;
use Mmb\Core\Client\Query\UploadFile;

class TelegramClient extends Client
{

    protected function execute(): mixed
    {
        if ($this->lowerMethod() == 'download') {
            return $this->executeDownload();
        }

        if ($this->hasUpload($this->parsedArgs())) {
            return $this->executeUpload();
        }

        return $this->executeRequest();
    }

    protected function newClient(): HttpClient
    {
        $client = new HttpClientBuilder();

        // Fire createdClient events
        // This callbacks will modify client
//        foreach (static::$createdClient as $callback) {
//            $callback->bindTo($this)($client);
//        }

        $options = [];
        foreach (static::$appendOptions as $option) {
            if (is_array($option)) {
                $options = $option + $options;
            } else {
                $options = $option->bindTo($this, static::class)($options) ?? $options;
            }
        }

        foreach ($options as $key => $value) {
            switch ($key) {
                case 'proxy':
                    $client = $client->usingPool(new UnlimitedConnectionPool(new DefaultConnectionFactory(new Http1TunnelConnector($value))));
                    break;

                default:
                    throw new \InvalidArgumentException("Option [$key] is not supported by [" . static::class . "]");
            }
        }

        return $client->build();
    }

    protected HttpClient $client;

    protected function getClient(): HttpClient
    {
        return $this->client ??= $this->newClient();
    }

    protected static array $appendOptions = [];

    public static function appendOptions(array|Closure $options): void
    {
        static::$appendOptions[] = $options;
    }

    protected function executeDownload(): bool
    {
        $args = $this->getJsonListArgs();

        $file = fopen($args['path'], 'w');
        $isSuccessful = false;

        try {
            $request = new Request(
                "https://api.telegram.org/file/bot{$this->token}/{$args['file']}",
                'GET',
                $args,
                isDownloadRequest: true,
            );

            $ampRequest = new AmpRequest($request->uri, $request->method);
            $ampRequest->setQueryParameters($request->parameters);

            $response = $this->getClient()->request($ampRequest);

            if ($response->getStatus() < 200 || $response->getStatus() >= 300) {
                // todo
                return false;
            }

            $stream = $response->getBody();
            while (null !== $chunk = $stream->read()) {
                fwrite($file, $chunk);
            }

            $isSuccessful = true;
            return true;
        } finally {
            fclose($file);

            if (!$isSuccessful && file_exists($file)) {
                unlink($file);
            }
        }
    }

    protected function executeUpload(): mixed
    {
        $request = new Request(
            "https://api.telegram.org/bot{$this->token}/{$this->method}",
            'POST',
            $this->getJsonListArgs(),
            isUploadRequest: true,
        );

        $multipart = [];
        foreach ($this->getJsonListArgs() as $key => $value) {
            if ($value instanceof \CURLFile) {
                $multipart[] = [
                    'name' => $key,
                    'contents' => fopen($value->getFilename(), 'rb'),
                    'filename' => basename($value->getFilename()),
                ];
            } elseif ($value instanceof UploadFile) {
                $multipart[] = [
                    'name' => $key,
                    'contents' => fopen($value->path, 'rb'),
                    'filename' => $value->fileName,
                ];
            } elseif ($value instanceof UploadContents) {
                $multipart[] = [
                    'name' => $key,
                    'contents' => $value->contents,
                    'filename' => $value->fileName,
                ];
            } elseif (is_resource($value)) {
                $metaData = stream_get_meta_data($value);

                $multipart[] = [
                    'name' => $key,
                    'contents' => $value,
                    'filename' => basename($metaData['uri']),
                ];
            } else {
                $multipart[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
        }

        $body = new MultipartStream($multipart);
        $ampRequest = new AmpRequest($request->uri, $request->method, $body);
        $ampRequest->setHeader('Content-Type', 'multipart/form-data; boundary=' . $body->getBoundary());

        return $this->requestJsonResult($ampRequest);
    }

    protected function executeRequest(): mixed
    {
        $request = new Request(
            "https://api.telegram.org/bot{$this->token}/{$this->method}",
            'POST',
            $this->getJsonListArgs(),
        );

        $ampRequest = new AmpRequest($request->uri, $request->method);
        $ampRequest->setQueryParameters($request->parameters);

        return $this->requestJsonResult($ampRequest);
    }

    protected function requestJsonResult(AmpRequest $ampRequest): mixed
    {
        $response = $this->getClient()->request($ampRequest);

        $json = @json_decode($response->getBody()->buffer(), true);

        if (!$json) {
            throw new TelegramResponseException("Invalid telegram response");
        }

        if (!@$json['ok']) {
            throw match ($json['error_code']) {
                default => new TelegramException("Telegram error: " . $json['description'] . " ($json[error_code])"),
            };
        }

        return $json['result'];
    }

}
