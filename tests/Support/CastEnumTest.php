<?php

namespace Mmb\Tests\Support;

use Mmb\Support\Caller\Caller;
use Mmb\Support\Cast\CastEnum;
use Mmb\Tests\TestCase;

class CastEnumTest extends TestCase
{

    public function test_cast_backed_enums()
    {
        Caller::invoke(
            function(#[CastEnum] EnumTest $test)
            {
                $this->assertSame(EnumTest::A, $test);
            },
            [0]
        );

        Caller::invoke(
            function(#[CastEnum] EnumTest $test)
            {
                $this->assertSame(EnumTest::B, $test);
            },
            [1]
        );

        Caller::invoke(
            function(#[CastEnum] ?EnumTest $test)
            {
                $this->assertSame(null, $test);
            },
            [999]
        );
    }

    public function test_cast_unit_enums()
    {
        Caller::invoke(
            function(#[CastEnum] UnitEnumTest $test)
            {
                $this->assertSame(UnitEnumTest::A, $test);
            },
            ['A']
        );

        Caller::invoke(
            function(#[CastEnum] UnitEnumTest $test)
            {
                $this->assertSame(UnitEnumTest::B, $test);
            },
            ['B']
        );

        Caller::invoke(
            function(#[CastEnum] ?UnitEnumTest $test)
            {
                $this->assertSame(null, $test);
            },
            ['Something else']
        );
    }

}

enum EnumTest: int
{
    case A = 0;
    case B = 1;
}

enum UnitEnumTest
{
    case A;
    case B;
}
