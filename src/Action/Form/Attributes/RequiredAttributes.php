<?php

namespace Mmb\Action\Form\Attributes;

use Attribute;
use Mmb\Action\Form\Exceptions\AttributeRequiredException;
use Mmb\Action\Form\Form;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RequiredAttributes implements FormClassModifierAttributeContract
{

    public const NOT_NULL = 1;
    public const NOT_EMPTY = 2;

    public function __construct(
        public array $attributes,
        public int   $mode = self::NOT_NULL,
    )
    {
    }

    public function registerFormClassModifier(Form $form)
    {
        $form->requesting(function () use ($form) {

            $missing = [];

            foreach ($this->attributes as $attr) {

                $value = $form->get($attr);

                if (match ($this->mode) {
                    self::NOT_NULL  => is_null($value),
                    self::NOT_EMPTY => empty($value),
                }) {
                    $missing[] = $attr;
                }

            }

            if (count($missing) == 1) {
                throw new AttributeRequiredException(
                    sprintf("Attribute [%s] is required in [%s]", $missing[0], get_class($form)),
                );
            }

            if ($missing) {
                throw new AttributeRequiredException(
                    sprintf("Attributes [%s] are required in [%s]", implode('], [', $missing), get_class($form)),
                );
            }

        });
    }

}