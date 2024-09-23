<?php

namespace Mmb\Tests\Support;

use BadMethodCallException;
use Mmb\Core\Updates\Files\Document;
use Mmb\Core\Updates\Files\Photo;
use Mmb\Core\Updates\Files\PhotoCollection;
use Mmb\Tests\TestCase;

class MacroableTest extends TestCase
{

    public function test_macro_is_working()
    {
        Photo::macro('test', fn() => 'OK');

        $this->assertSame(Photo::test(), 'OK');
        $this->assertSame(PhotoCollection::test(), 'OK');

        $this->expectException(BadMethodCallException::class);
        Document::test();
    }

}