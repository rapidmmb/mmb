<?php

namespace Mmb\Tests\Action\Road;

use Illuminate\Support\Str;
use Mmb\Action\Road\Road;
use Mmb\Action\Road\Sign;
use Mmb\Action\Road\Station;
use Mmb\Core\Updates\Update;
use Mmb\Support\Encoding\Modes\Mode;
use Mmb\Support\Encoding\Text;
use Mmb\Tests\TestCase;

class ExtendsTestSignTest extends TestCase
{

    public function test_define_method()
    {
        $sign = new class(new Road($this->context)) extends _TestSign
        {
            protected function boot()
            {
                parent::boot();
                $this->defineMethod('test', fn () => 'Foo');
                $this->defineMethod('test2', fn ($arg) => $arg);
            }
        };

        $this->assertSame('Foo', $sign->test());
        $this->assertSame('Bar', $sign->test2('Bar'));

        $this->expectException(\BadMethodCallException::class);
        $sign->undefinedMethod();
    }

    public function test_define_label()
    {
        $road = new Road($this->context);
        $sign = new class($road) extends _TestSign
        {
            protected function boot()
            {
                parent::boot();

                $this->defineLabel('testLabel');
            }

            protected function onTestLabel()
            {
                return 'Foo';
            }

            protected function onTestLabelUsing($string)
            {
                return "#$string#";
            }

            public function getTestLabel(_TestStation $station)
            {
                return $this->getDefinedLabel($station, 'testLabel');
            }
        };
        $station = new _TestStation($road, $sign, 'test');

        $this->assertSame('#Foo#', $sign->getTestLabel($station));

        $sign->testLabel(fn () => 'Bar');
        $this->assertSame('#Bar#', $sign->getTestLabel($station));

        $sign->testLabel(fn () => 'SomeText');
        $this->assertSame('#SomeText#', $sign->getTestLabel($station));

        $sign->testLabelUsing(fn ($str) => Str::kebab($str));
        $this->assertSame('#some-text#', $sign->getTestLabel($station));

        $sign->testLabelPrefix('<');
        $sign->testLabelSuffix(fn () => '>');
        $this->assertSame('#<some-text>#', $sign->getTestLabel($station));
    }

    public function test_define_message()
    {
        $road = new Road($this->context);
        $sign = new class($road) extends _TestSign
        {
            protected function boot()
            {
                parent::boot();

                $this->defineMessage('message');
            }

            protected function onMessage()
            {
                return [
                    'extra' => 'Foo',
                ];
            }

            protected function onMessageMode()
            {
                return 'MarkDown2';
            }

            protected function onMessageUsing($message)
            {
                $message['type'] ??= 'text';
                return $message;
            }

            protected function onMessageTextUsing($text, Mode $mode)
            {
                return $mode->string('[') . $text . $mode->string(']');
            }

            public function getMessage(_TestStation $station)
            {
                return $this->getDefinedMessage($station, 'message');
            }
        };
        $station = new _TestStation($road, $sign, 'test');

        $markdown2 = Text::mode('MarkDown2');
        $html = Text::mode('Html');

        $this->assertSame(
            ['extra' => 'Foo', 'type' => 'text', 'text' => (string) $markdown2->string('[]')],
            $sign->getMessage($station)
        );

        $sign->message(['extra' => 'Bar']);
        $this->assertSame(
            ['extra' => 'Bar', 'text' => (string) $markdown2->string('[]'), 'type' => 'text'],
            $sign->getMessage($station)
        );

        $sign->message(fn (Mode $mode) => ['type' => 'photo', 'text' => $mode->string('<Hi>')]);
        $this->assertSame(
            ['type' => 'photo', 'text' => (string) $markdown2->string('[<Hi>]')],
            $sign->getMessage($station)
        );

        $sign->message(['type' => 'photo', 'text' => '<Hi>']);
        $this->assertSame(
            ['type' => 'photo', 'text' => (string) $markdown2->string('[<Hi>]')],
            $sign->getMessage($station)
        );

        $sign->messageMode($html);
        $this->assertSame(
            ['type' => 'photo', 'text' => (string) $html->string('[<Hi>]')],
            $sign->getMessage($station)
        );

        $sign->messageUsing(
            function ($message)
            {
                $message['type'] = 'text';
                return $message;
            }
        );
        $this->assertSame(
            ['type' => 'text', 'text' => (string) $html->string('[<Hi>]')],
            $sign->getMessage($station)
        );

        $sign->messageTextUsing(fn ($text, Mode $mode) => $mode->bold($mode->trust($text)));
        $this->assertSame(
            ['type' => 'text', 'text' => (string) $html->build('[', $html->bold('<Hi>'), ']')],
            $sign->getMessage($station)
        );

        $sign->messagePrefix('<pre>');
        $sign->messageSuffix('</pre>');
        $this->assertSame(
            [
                'type' => 'text', 'text' => (string) $html->build(
                '[<pre>', $html->bold('<Hi>'), '</pre>]'
            ),
            ],
            $sign->getMessage($station)
        );
    }

}

class _TestSign extends Sign
{
    public function createStation(string $name) : Station
    {
        return value(null); // Just ignore errors
    }
}

class _TestStation extends Station
{

}
