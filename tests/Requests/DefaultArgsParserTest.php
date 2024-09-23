<?php

namespace Mmb\Tests\Requests;

use Mmb\Core\Requests\Parser\DefaultArgsParser;
use Mmb\Core\Requests\TelegramRequest;
use Mmb\Tests\TestCase;

class DefaultArgsParserTest extends TestCase
{

    public DefaultArgsParser $parser;

    protected function setUp() : void
    {
        parent::setUp();

        $this->parser = new DefaultArgsParser();
    }

    public function createRequest(string $method, array $args)
    {
        return new TelegramRequest(bot(), '', $method, $args);
    }

    public function assertParsed(array $actual, array $args, string $method = '')
    {
        $this->assertSame($actual, $this->parser->normalize($this->createRequest($method, $args)));
    }


    public function test_camel_case_is_used()
    {
        $this->assertParsed(
            ['example_camel_case' => 1234],
            ['exampleCamelCase' => 1234],
        );

        $this->assertParsed(
            ['also_normal_argument_is_ok' => 1234],
            ['also_normal_argument_is_ok' => 1234],
        );
    }

    public function test_chat_id_aliases()
    {
        $this->assertParsed(
            ['chat_id' => 1234],
            ['chat' => 1234],
        );

        $this->assertParsed(
            ['chat_id' => 1234],
            ['chatId' => 1234],
        );
    }

    public function test_message_id_aliases()
    {
        $this->assertParsed(
            ['message_id' => 1234],
            ['message' => 1234],
        );

        $this->assertParsed(
            ['message_id' => 1234],
            ['messageId' => 1234],
        );

        $this->assertParsed(
            ['message_id' => 1234],
            ['msg' => 1234],
        );

        $this->assertParsed(
            ['message_id' => 1234],
            ['msgId' => 1234],
        );
    }

    public function test_id_auto_detection()
    {
        $this->assertParsed(
            ['chat_id' => 1234],
            ['id' => 1234],
        );

        $this->assertParsed(
            ['callback_query_id' => 1234],
            ['id' => 1234],
            'answerCallbackQuery',
        );

        $this->assertParsed(
            ['inline_query_id' => 1234],
            ['id' => 1234],
            'answerInlineQuery'
        );

        $this->assertParsed(
            ['file_id' => 1234],
            ['id' => 1234],
            'getFile'
        );
    }

    public function test_parse_text()
    {
        $this->assertParsed(
            ['text' => 'Foo'],
            ['text' => 'Foo'],
            'sendMessage',
        );

        $this->assertParsed(
            ['caption' => 'Foo'],
            ['text' => 'Foo'],
            'copyMessage',
        );

        $this->assertParsed(
            ['question' => 'Foo'],
            ['text' => 'Foo'],
            'sendPoll',
        );

        $this->assertParsed(
            ['caption' => 'Foo'],
            ['text' => 'Foo'],
            'sendAnythingElse',
        );

        $this->assertParsed(
            ['text' => 'Foo'],
            ['text' => 'Foo'],
            'editMessageText',
        );

        $this->assertParsed(
            ['caption' => 'Foo'],
            ['text' => 'Foo'],
            'editAnythingElse',
        );

        $this->assertParsed(
            ['text' => 'Foo'],
            ['text' => 'Foo'],
            'other',
        );
    }

    public function test_mode()
    {
        $this->assertParsed(
            ['parse_mode' => 'HTML'],
            ['mode' => 'HTML'],
        );
    }

    public function test_reply()
    {
        $this->assertParsed(
            ['reply_to_message_id' => 1234],
            ['reply' => 1234],
        );

        $this->assertParsed(
            ['reply_to_message_id' => 1234],
            ['replyTo' => 1234],
        );

        $this->assertParsed(
            ['reply_to_message_id' => 1234],
            ['replyMessage' => 1234],
        );

        $this->assertParsed(
            ['reply_to_message_id' => 1234],
            ['replyToMessage' => 1234],
        );
    }

}