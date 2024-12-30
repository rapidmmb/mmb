<?php

namespace Mmb\Tests\Action\Menu;

use Mmb\Action\Section\Menu;
use Mmb\Action\Section\Section;
use Mmb\Core\Updates\Infos\ChatFaker;
use Mmb\Core\Updates\Infos\UserFaker;
use Mmb\Core\Updates\Messages\MessageFaker;
use Mmb\Core\Updates\UpdateFaker;
use Mmb\Support\Action\ActionCallback;
use Mmb\Support\KeySchema\KeyUniqueData;
use Mmb\Tests\TestCase;

class TestMenuBehaviour extends TestCase
{

    public function test_creating()
    {
        $section = new class($this->context) extends Section {

            public function test(Menu $menu)
            {
            }

        };

        $this->assertInstanceOf(Menu::class, $section->test->make());
    }

    public function test_keyboard_schema()
    {
        $section = new class($this->context) extends Section {

            public function test(Menu $menu)
            {
                $menu
                    ->footer([
                        [$menu->key("Footer")],
                    ])
                    ->header([
                        [$menu->key("Header")],
                    ])
                    ->schema([
                        [$menu->key("A")],
                        [$menu->key("B"), $menu->key("C")],
                    ])
                    ->header([
                        [$menu->key("SubHeader")],
                    ]);
            }

        };

        $menu = $section->menu('test');
        $menu->makeReady();
        $menu->assertKeyboardArray([
            [['text' => 'Header']],
            [['text' => 'SubHeader']],
            [['text' => 'A']],
            [['text' => 'B'], ['text' => 'C']],
            [['text' => 'Footer']],
        ]);
    }

    public function test_key_map()
    {
        $section = new class($this->context) extends Section {

            public function test(Menu $menu)
            {
                $menu
                    ->schema([
                        [$menu->key("A")], // Override by next key
                        [$menu->key("A", 'first', 1, 2)],
                        [$menu->key("B", 'second'), $menu->key("C", 'third')],
                        [$menu->key("D", new ActionCallback('x', [1]), 2)],
                    ]);
            }

        };

        $menu = $section->menu('test');
        $menu->store();
        $menu->makeReady();
        $menu->assertStorableKeyMap([
            KeyUniqueData::makeText('A') => (new ActionCallback('first', [1, 2]))->toArray(),
            KeyUniqueData::makeText('B') => (new ActionCallback('second'))->toArray(),
            KeyUniqueData::makeText('C') => (new ActionCallback('third'))->toArray(),
            KeyUniqueData::makeText('D') => (new ActionCallback('x', [1, 2]))->toArray(),
        ]);
    }

    public function test_detect_key()
    {
        $section = new class($this->context) extends Section {

            public function test(Menu $menu)
            {
                $menu
                    ->schema([
                        [$menu->key("Text", 'text')],
                    ]);
            }

        };

        $menu = $section->menu('test');
        $menu->makeReady();
        $action = $menu->findClickedKeyAction(
            UpdateFaker::message(
                MessageFaker::text(1, UserFaker::make(2), ChatFaker::private(2), "Text"),
            ),
        );

        $this->assertNotNull($action);
        $this->assertSame('text', $action->action);
    }

    public function test_handle_click_key()
    {
        $section = new class($this->context) extends Section {

            public bool $ok = false;

            public function test(Menu $menu)
            {
                $menu
                    ->schema([
                        [$menu->key("Text", fn() => $this->ok = true)],
                    ]);
            }

        };

        $menu = $section->menu('test');
        $menu->makeReady();
        $menu->handle(
            UpdateFaker::message(
                MessageFaker::text(1, UserFaker::make(2), ChatFaker::private(2), "Text"),
            ),
        );

        $this->assertTrue($section->ok);
    }

}