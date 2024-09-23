<?php

namespace Mmb\Tests\Action\Form;

use Illuminate\Support\Str;
use Mmb\Action\Form\Attributes\AsAttribute;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\HasFormBacks;
use Mmb\Action\Section\Section;
use Mmb\Auth\AreaRegister;
use Mmb\Tests\TestCase;

class FormWithBacksTest extends TestCase
{

    public function test_defined_back()
    {
        $form = new class extends Form
        {
            use HasFormBacks;

            #[AsAttribute]
            public $back = [_FormWithBacksTestAction::class, 'main'];

            protected $inputs = [];
        };

        _FormWithBacksTestAction::$isCalled = false;
        $form->startForm();
        $form->back();
        $this->assertTrue(_FormWithBacksTestAction::$isCalled);
    }

    public function test_auto_back_using_area()
    {
        $form = new class extends Form
        {
            use HasFormBacks;

            protected $inputs = [];
        };

        app()->singleton(AreaRegister::class);
        app(AreaRegister::class)->putForClass(get_class($form), 'back', [_FormWithBacksTestAction::class, 'main']);

        _FormWithBacksTestAction::$isCalled = false;
        $form->startForm();
        $form->back();
        $this->assertTrue(_FormWithBacksTestAction::$isCalled);
    }

    public function test_auto_back_using_area_namespace()
    {
        $form = new class extends Form
        {
            use HasFormBacks;

            protected $inputs = [];
        };

        app()->singleton(AreaRegister::class);
        app(AreaRegister::class)->putForNamespace(Str::beforeLast(get_class($form), '\\'), 'back', [_FormWithBacksTestAction::class, 'main']);

        _FormWithBacksTestAction::$isCalled = false;
        $form->startForm();
        $form->back();
        $this->assertTrue(_FormWithBacksTestAction::$isCalled);
    }

    public function test_auto_back_using_custom_area()
    {
        $form = new class extends Form
        {
            use HasFormBacks;

            protected $inputs = [];
        };

        app()->singleton(AreaRegister::class);
        app(AreaRegister::class)->putForClass('Bar', 'back', [_FormWithBacksTestAction::class, 'main']);
        $form->withBackOfArea('Bar');

        _FormWithBacksTestAction::$isCalled = false;
        $form->startForm();
        $form->back();
        $this->assertTrue(_FormWithBacksTestAction::$isCalled);
    }

    public function test_back_in_cancel()
    {
        $form = new class extends Form
        {
            use HasFormBacks;

            #[AsAttribute]
            public $back = [_FormWithBacksTestAction::class, 'main'];

            protected $inputs = [];
        };

        _FormWithBacksTestAction::$isCalled = false;
        $form->startForm();
        $form->fire('cancel');
        $this->assertTrue(_FormWithBacksTestAction::$isCalled);
    }

}

class _FormWithBacksTestAction extends Section
{
    public static bool $isCalled;

    public function main()
    {
        static::$isCalled = true;
    }
}
