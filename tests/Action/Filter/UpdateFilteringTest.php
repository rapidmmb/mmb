<?php

namespace Mmb\Tests\Action\Filter;

use Mmb\Action\Filter\Filter;
use Mmb\Action\Filter\FilterFailException;
use Mmb\Core\Updates\Infos\ChatFaker;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\UserFaker;
use Mmb\Core\Updates\Infos\UserInfo;
use Mmb\Core\Updates\Messages\MessageFaker;
use Mmb\Core\Updates\Update;
use Mmb\Core\Updates\UpdateFaker;
use Mmb\Tests\TestCase;
use PHPUnit\Framework\Constraint\Constraint;

class UpdateFilteringTest extends TestCase
{

    protected ChatInfo $fakeChat;
    protected UserInfo $fakeUser;

    protected function setUp() : void
    {
        parent::setUp();
        $this->fakeChat = ChatFaker::private(12345);
        $this->fakeUser = UserFaker::make(12345);
    }

    public function assertSuccess($value, Filter $filter, Update $update)
    {
        $this->assertSame($value, $filter->filter($update));
    }

    public function assertFail(string $description = null, Filter $filter, Update $update)
    {
        try
        {
            $filter->filter($update);
            $this->assertTrue(false, "Expected filter failing, but not failed");
        }
        catch (FilterFailException $e)
        {
            $this->assertTrue(true);
            $this->assertSame($description, $e->description);
        }
    }



    public function test_simple_working()
    {
        $update = UpdateFaker::message(MessageFaker::simpleText("Hi"));

        $this->assertSuccess('Hi', Filter::make()->text(), $update);
        $this->assertSame("Hi", Filter::make()->message()->filter($update)?->text);

        // $this->assertFail(Filter::make()->int(), $update);
    }


    public function test_message_filter()
    {
        $filter = Filter::make()->message('Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("Foo"));
        $this->assertSuccess($update->message, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document'));
        $this->assertSuccess($update->message, $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText('Foo'));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_media_filter()
    {
        $filter = Filter::make()->media('Media', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("Foo"));
        $this->assertFail('Media', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document'));
        $this->assertSuccess($update->message->media, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('photo'));
        $this->assertSuccess($update->message->media, $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText('Foo'));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_media_or_text_filter()
    {
        $filter = Filter::make()->mediaOrText('Media', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("Foo"));
        $this->assertSuccess('Foo', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document'));
        $this->assertSuccess($update->message->media, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('photo'));
        $this->assertSuccess($update->message->media, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('anything-else'));
        $this->assertFail('Media', $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText('Foo'));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_text_filter()
    {
        $filter = Filter::make()->text('Text', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("Foo"));
        $this->assertSuccess('Foo', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Foo\nBar"));
        $this->assertSuccess("Foo\nBar", $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document'));
        $this->assertFail('Text', $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText('Foo'));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_text_single_line_filter()
    {
        $filter = Filter::make()->textSingleLine('Single', 'Text', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("Foo"));
        $this->assertSuccess('Foo', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Foo\nBar"));
        $this->assertFail('Single', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document'));
        $this->assertFail('Text', $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText('Foo'));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_integer_filter()
    {
        $filter = Filter::make()->int('Number', 'Text', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("1234"));
        $this->assertSuccess(1234, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("0"));
        $this->assertSuccess(0, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-1234"));
        $this->assertSuccess(-1234, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("3.14"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-3.14"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Bar"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document', text: '1234'));
        $this->assertFail('Text', $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText("Bar"));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_unsigned_integer_filter()
    {
        $filter = Filter::make()->unsignedInt('Number', 'Text', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("1234"));
        $this->assertSuccess(1234, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("0"));
        $this->assertSuccess(0, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-1234"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("3.14"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-3.14"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Bar"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document', text: '1234'));
        $this->assertFail('Text', $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText("Bar"));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_float_filter()
    {
        $filter = Filter::make()->float('Number', 'Text', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("3.14"));
        $this->assertSuccess(3.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("3."));
        $this->assertSuccess(3., $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText(".14"));
        $this->assertSuccess(.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("0"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-3.14"));
        $this->assertSuccess(-3.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("1234"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-1234"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Bar"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document', text: '1234'));
        $this->assertFail('Text', $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText("Bar"));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_unsigned_float_filter()
    {
        $filter = Filter::make()->unsignedFloat('Number', 'Text', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("3.14"));
        $this->assertSuccess(3.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("3."));
        $this->assertSuccess(3., $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText(".14"));
        $this->assertSuccess(.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("0"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-3.14"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("1234"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-1234"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Bar"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document', text: '1234'));
        $this->assertFail('Text', $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText("Bar"));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_number_filter()
    {
        $filter = Filter::make()->number('Number', 'Text', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("3.14"));
        $this->assertSuccess(3.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("3."));
        $this->assertSuccess(3., $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText(".14"));
        $this->assertSuccess(.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("0"));
        $this->assertSuccess(0, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-3.14"));
        $this->assertSuccess(-3.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("1234"));
        $this->assertSuccess(1234, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-1234"));
        $this->assertSuccess(-1234, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Bar"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document', text: '1234'));
        $this->assertFail('Text', $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText("Bar"));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_unsigned_number_filter()
    {
        $filter = Filter::make()->unsignedNumber('Number', 'Text', 'Message');

        $update = UpdateFaker::message(MessageFaker::simpleText("3.14"));
        $this->assertSuccess(3.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("3."));
        $this->assertSuccess(3., $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText(".14"));
        $this->assertSuccess(.14, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("0"));
        $this->assertSuccess(0, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-3.14"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("1234"));
        $this->assertSuccess(1234, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("-1234"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Bar"));
        $this->assertFail('Number', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleMedia('document', text: '1234'));
        $this->assertFail('Text', $filter, $update);

        $update = UpdateFaker::editedMessage(MessageFaker::simpleText("Bar"));
        $this->assertFail('Message', $filter, $update);
    }

    public function test_clamp_filter()
    {
        $filter = Filter::make()->number()->clamp(5, 10, 'Min', 'Max');

        $update = UpdateFaker::message(MessageFaker::simpleText("7"));
        $this->assertSuccess(7, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("5"));
        $this->assertSuccess(5, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("10"));
        $this->assertSuccess(10, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("2"));
        $this->assertFail('Min', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("20"));
        $this->assertFail('Max', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("4.99"));
        $this->assertFail('Min', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("10.01"));
        $this->assertFail('Max', $filter, $update);

        $filter = Filter::make()->number()->clamp(5, 10, error: 'Error');

        $update = UpdateFaker::message(MessageFaker::simpleText("2"));
        $this->assertFail('Error', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("20"));
        $this->assertFail('Error', $filter, $update);
    }

    public function test_length_filter()
    {
        $filter = Filter::make()->text()->length(5, 10, 'Min', 'Max');

        $update = UpdateFaker::message(MessageFaker::simpleText("Foo Bar"));
        $this->assertSuccess('Foo Bar', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Hello"));
        $this->assertSuccess('Hello', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("HelloWorld"));
        $this->assertSuccess('HelloWorld', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Hi"));
        $this->assertFail('Min', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Hello To The World"));
        $this->assertFail('Max', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("1234"));
        $this->assertFail('Min', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("1234567890_"));
        $this->assertFail('Max', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("سلام دنیا"));
        $this->assertSuccess('سلام دنیا', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("سلام"));
        $this->assertFail('Min', $filter, $update);

        $filter = Filter::make()->text()->length(5, 10, error: 'Error', ascii: false);

        $update = UpdateFaker::message(MessageFaker::simpleText("Hi"));
        $this->assertFail('Error', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("Hello To The World"));
        $this->assertFail('Error', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("سلام دنیا"));
        $this->assertFail('Error', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("سلام"));
        $this->assertSuccess('سلام', $filter, $update);
    }

    public function test_divisible_filter()
    {
        $filter = Filter::make()->int()->divisible(5, 'Div');

        $update = UpdateFaker::message(MessageFaker::simpleText("15"));
        $this->assertSuccess(15, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("255"));
        $this->assertSuccess(255, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("0"));
        $this->assertSuccess(0, $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("17"));
        $this->assertFail('Div', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("1"));
        $this->assertFail('Div', $filter, $update);
    }

    public function test_regex_filter()
    {
        $filter = Filter::make()->text()->regex('/s+/', error: 'Regex');

        $update = UpdateFaker::message(MessageFaker::simpleText("test"));
        $this->assertSuccess('test', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("foo"));
        $this->assertFail('Regex', $filter, $update);

        $filter = Filter::make()->text()->regex('/s+/', 0, error: 'Regex');

        $update = UpdateFaker::message(MessageFaker::simpleText("test"));
        $this->assertSuccess('s', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("tesssst"));
        $this->assertSuccess('ssss', $filter, $update);

        $filter = Filter::make()->text()->regex('/(s)(t+)/', 2, error: 'Regex');

        $update = UpdateFaker::message(MessageFaker::simpleText("test"));
        $this->assertSuccess('t', $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("tesssstttt"));
        $this->assertSuccess('tttt', $filter, $update);

        $filter = Filter::make()->text()->regex('/(s)(t+)/', '*', error: 'Regex');

        $update = UpdateFaker::message(MessageFaker::simpleText("test"));
        $this->assertSuccess(['st', 's', 't'], $filter, $update);

        $update = UpdateFaker::message(MessageFaker::simpleText("tesssstttt"));
        $this->assertSuccess(['stttt', 's', 'tttt'], $filter, $update);
    }

}