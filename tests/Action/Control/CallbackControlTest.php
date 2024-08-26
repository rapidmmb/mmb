<?php

namespace Action\Control;

use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Section\Controllers\Attributes\OnCallback;
use Mmb\Action\Section\Controllers\CallbackControl;
use Mmb\Action\Section\Controllers\QueryMatcher;
use Mmb\Action\Section\Section;
use Mmb\Support\Db\Attributes\FindById;
use Mmb\Support\Db\FinderFactory;
use Mmb\Support\Db\ModelFinder;
use Mmb\Tests\TestCase;

class CallbackControlTest extends TestCase
{

    public function test_create_key()
    {
        $section = new class extends Section
        {
            use CallbackControl;

            public function onCallback(QueryMatcher $matcher)
            {
                $matcher->match('foo');
            }
        };

        $this->assertSame(['text' => 'Bar', 'data' => 'foo'], $section->keyInline('Bar'));
    }

    public function test_with_arguments()
    {
        $section = new class extends Section
        {
            use CallbackControl;

            public function onCallback(QueryMatcher $matcher)
            {
                $matcher->match('foo:{number:int}');
            }
        };

        $this->assertSame(['text' => 'Bar', 'data' => 'foo:10'], $section->keyInline('Bar', 10));
    }

    public function test_auto_match()
    {
        $section = new class extends Section
        {
            use CallbackControl;

            #[OnCallback('foo', true)]
            public function foo()
            {
            }
        };

        $this->assertSame(['text' => 'Bar', 'data' => 'foo'], $section->keyInline('Bar'));
    }

    public function test_auto_match_with_arguments()
    {
        $section = new class extends Section
        {
            use CallbackControl;

            #[OnCallback]
            public function foo(int $number)
            {
            }
        };

        $key = $section->keyInline('Bar', 'foo', 10);
        $this->assertSame('Bar', $key['text']);
        $this->assertStringEndsWith(':foo:[10]', $key['data']);
    }

    public function test_auto_match_invokes()
    {
        $section = new class extends Section
        {
            use CallbackControl;

            #[OnCallback]
            public function foo(int $number)
            {
                return 'FooBar';
            }
        };

        $key = $section->keyInline('Bar', 'foo', 10);
        $pattern = $section->getCallbackMatcher()->findPattern($key['data']);

        $this->assertSame('FooBar', $pattern->invoke($section));
    }

    public function test_auto_match_has_different_queries_for_each_class()
    {
        $sectionA = new class extends Section
        {
            use CallbackControl;

            #[OnCallback]
            public function foo()
            {
            }

            #[OnCallback('custom')]
            public function bar()
            {
            }
        };
        $sectionB = new class extends Section
        {
            use CallbackControl;

            #[OnCallback]
            public function foo()
            {
            }

            #[OnCallback('custom')]
            public function bar()
            {
            }
        };

        $this->assertNotSame($sectionA->keyInline('foo'), $sectionB->keyInline('foo'), "Different sections generate equal queries");
        $this->assertNotSame($sectionA->keyInline('bar'), $sectionB->keyInline('bar'), "Different sections generate equal queries");
    }

    public function test_auto_match_has_same_queries_when_using_full_mode()
    {
        $sectionA = new class extends Section
        {
            use CallbackControl;

            #[OnCallback('fully', true)]
            public function foo()
            {
            }
        };
        $sectionB = new class extends Section
        {
            use CallbackControl;

            #[OnCallback('fully', true)]
            public function foo()
            {
            }
        };

        $this->assertSame($sectionA->keyInline('foo'), $sectionB->keyInline('foo'), "Different sections generate different queries");
    }

    public function test_auto_match_customized()
    {
        $section = new class extends Section
        {
            use CallbackControl;

            #[OnCallback('customized:{number:int}')]
            public function foo(int $number)
            {
                return 'FooBar';
            }
        };

        $key = $section->keyInline('Bar', 10);
        $this->assertStringEndsWith(':customized:10', $key['data']);

        $pattern = $section->getCallbackMatcher()->findPattern($key['data']);
        $this->assertSame('FooBar', $pattern->invoke($section));
    }

    public function test_auto_match_customized_with_name_parameter()
    {
        $section = new class extends Section
        {
            use CallbackControl;

            #[OnCallback('{_}:{number:int}')]
            public function foo(int $number)
            {
                return 'FooBar';
            }
        };

        $key = $section->keyInline('Bar', 'foo', 10);
        $this->assertStringEndsWith(':foo:10', $key['data']);

        $pattern = $section->getCallbackMatcher()->findPattern($key['data']);
        $this->assertSame('FooBar', $pattern->invoke($section));
    }

    public function test_model_argument_with_find_attribute()
    {
        $section = new class extends Section
        {
            use CallbackControl;

            #[OnCallback]
            public function foo(#[FindById] _CallbackControlTestModel $record)
            {
                return $record->id;
            }
        };

        app()->singleton(FinderFactory::class);
        ModelFinder::store(new _CallbackControlTestModel([
            'id' => 10,
        ]));

        $key = $section->keyInline('Bar', 'foo', 10);

        $pattern = $section->getCallbackMatcher()->findPattern($key['data']);
        $this->assertSame(10, $pattern->invoke($section));
    }

}

class _CallbackControlTestModel extends Model
{
    protected $fillable = ['id'];
}