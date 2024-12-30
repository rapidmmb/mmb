<?php

namespace Mmb\Tests\Action\Form;

use Mmb\Action\Form\Attributes\AsAttribute;
use Mmb\Action\Form\Attributes\Required;
use Mmb\Action\Form\Attributes\RequiredAttributes;
use Mmb\Action\Form\Exceptions\AttributeRequiredException;
use Mmb\Action\Form\Form;
use Mmb\Tests\TestCase;

class FormAttributeRulesTest extends TestCase
{

    public function test_required_an_attribute()
    {
        $form = new class ($this->context) extends Form {
            protected $inputs = [];

            #[Required]
            #[AsAttribute]
            public int $test;

            protected function onFinish()
            {
            }
        };

        $this->expectException(AttributeRequiredException::class);
        $this->expectExceptionMessageMatches('/^Attribute \[test\] is required/');
        $form->request();
    }

    public function test_required_an_attribute_passed()
    {
        $form = new class ($this->context) extends Form {
            protected $inputs = [];

            #[Required]
            #[AsAttribute]
            public int $test;

            protected function onFinish()
            {
            }
        };

        $form->request(['test' => 0]);
        $this->assertTrue(true);
    }

    public function test_required_an_attribute_with_empty_mode()
    {
        $form = new class ($this->context) extends Form {
            protected $inputs = [];

            #[Required(Required::NOT_EMPTY)]
            #[AsAttribute]
            public int $test;

            protected function onFinish()
            {
            }
        };

        $this->expectException(AttributeRequiredException::class);
        $this->expectExceptionMessageMatches('/^Attribute \[test\] is required/');
        $form->request(['test' => 0]);
    }

    public function test_required_class_attribute()
    {
        $form = new #[RequiredAttributes(['a', 'b'])] class ($this->context) extends Form {
            protected $inputs = [];

            protected function onFinish()
            {
            }
        };

        $this->expectException(AttributeRequiredException::class);
        $this->expectExceptionMessageMatches('/^Attributes \[a\], \[b\] are required/');
        $form->request();
    }

}