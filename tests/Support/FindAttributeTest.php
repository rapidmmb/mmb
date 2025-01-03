<?php

namespace Mmb\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Exceptions\AbortException;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Db\Attributes\Find;
use Mmb\Support\Db\Attributes\FindBy;
use Mmb\Support\Db\Attributes\FindById;
use Mmb\Support\Db\Attributes\FindDynamic;
use Mmb\Support\Db\ModelFinder;
use Mmb\Tests\TestCase;

class FindAttributeTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->context->put(ModelFinder::class, new class($this->context) extends ModelFinder {
            public function find(
                string $model, $id, $default = null, ?string $by = null,
                bool   $withTrashed = false, null|int|string|true $orFail = null,
                mixed  $failMessage = null, bool $useCache = true,
            )
            {
                if ($id == '404') {
                    return $this->runDefault($default, $orFail, $failMessage);
                }

                return new $model([
                        $by ?? 'id' => $id,
                ]);
            }
        });
    }

    public function test_find_attribute()
    {
        Caller::invoke(
            $this->context,
            function (int $id, #[Find] TestModel $test) {
                $this->assertSame($id, $test->id);
            },
            [1234, 1234],
        );

        Caller::invoke(
            $this->context,
            function (int $id, #[Find(by: 'foo')] TestModel $test) {
                $this->assertSame($id, $test->foo);
            },
            [1234, 1234],
        );

        $this->expectException(AbortException::class);

        Caller::invoke(
            $this->context,
            function (int $id, #[FindById] TestModel $test) {
                $this->assertTrue(false, "Should not call this section!");
            },
            ['404', '404'],
        );
    }

    public function test_find_with_null_value()
    {
        Caller::invoke(
            $this->context,
            function (#[Find] TestModel|null $test) {
                $this->assertNull($test);
            },
            [null],
        );

        $this->expectException(AbortException::class);

        Caller::invoke(
            $this->context,
            function (#[Find] TestModel $test) {
                $this->assertTrue(false, "Should not call this section!");
            },
            [null],
        );
    }

    public function test_find_with_not_exists_value()
    {
        Caller::invoke(
            $this->context,
            function (#[Find(nullOnFail: true)] TestModel|null $test) {
                $this->assertNull($test);
            },
            ['404'],
        );

        $this->expectException(AbortException::class);

        Caller::invoke(
            $this->context,
            function (#[Find] TestModel|null $test) {
                $this->assertTrue(false, "Should not call this section!");
            },
            ['404'],
        );
    }

    public function test_find_by_id()
    {
        Caller::invoke(
            $this->context,
            function (int $id, #[FindById] TestModel $test) {
                $this->assertSame($id, $test->id);
            },
            [1234, 1234],
        );

        $this->expectException(AbortException::class);

        Caller::invoke(
            $this->context,
            function (int $id, #[FindById] TestModel $test) {
                $this->assertTrue(false, "Should not call this section!");
            },
            ['404', '404'],
        );
    }

    public function test_find_by()
    {
        Caller::invoke(
            $this->context,
            function ($value, #[FindBy('foo')] TestModel $test) {
                $this->assertSame($value, $test->foo);
            },
            ['bar', 'bar'],
        );

        $this->expectException(AbortException::class);

        Caller::invoke(
            $this->context,
            function (int $id, #[FindById] TestModel $test) {
                $this->assertTrue(false, "Should not call this section!");
            },
            ['404', '404'],
        );
    }

    // public function test_find_by_repo1()
    // {
    //     $this->runWithPov(
    //         function ()
    //         {
    //             Caller::invoke(
    //                 function ($id, #[FindByRepo(TestRepo1::class)] TestModel $test)
    //                 {
    //                     $this->assertSame($id, $test->id);
    //                 },
    //                 [1234, 1234]
    //             );
    //
    //             $this->expectException(AbortException::class);
    //
    //             Caller::invoke(
    //                 function (int $id, #[FindByRepo(TestRepo1::class)] TestModel $test)
    //                 {
    //                     $this->assertTrue(false, "Should not call this section!");
    //                 },
    //                 ['404', '404']
    //             );
    //         }
    //     );
    // }
    //
    // public function test_find_by_repo2()
    // {
    //     $this->runWithPov(
    //         function ()
    //         {
    //             Caller::invoke(
    //                 function ($id, #[FindByRepo(TestRepo2::class)] TestModel $test)
    //                 {
    //                     $this->assertSame($id, $test->id);
    //                 },
    //                 [1234, 1234]
    //             );
    //
    //             $this->expectException(AbortException::class);
    //
    //             Caller::invoke(
    //                 function (int $id, #[FindByRepo(TestRepo2::class)] TestModel $test)
    //                 {
    //                     $this->assertTrue(false, "Should not call this section!");
    //                 },
    //                 ['404', '404']
    //             );
    //         }
    //     );
    // }
    //
    // public function test_find_by_repo3()
    // {
    //     $this->runWithPov(
    //         function ()
    //         {
    //             Caller::invoke(
    //                 function ($id, #[FindByRepo(TestRepo3::class)] TestModel $test)
    //                 {
    //                     $this->assertSame($id, $test->id);
    //                 },
    //                 [1234, 1234]
    //             );
    //
    //             $this->expectException(AbortException::class);
    //
    //             Caller::invoke(
    //                 function (int $id, #[FindByRepo(TestRepo3::class)] TestModel $test)
    //                 {
    //                     $this->assertTrue(false, "Should not call this section!");
    //                 },
    //                 ['404', '404']
    //             );
    //         }
    //     );
    // }

    public function test_find_dynamic()
    {
        Caller::invoke(
            $this->context,
            function (int $id, #[FindDynamic] TestModel|TestModel2 $test) {
                $this->assertInstanceOf(TestModel::class, $test);
                $this->assertSame($id, $test->id);
            },
            ['1234', 'TestModel:1234'],
        );

        Caller::invoke(
            $this->context,
            function (int $id, #[FindDynamic] TestModel|TestModel2 $test) {
                $this->assertInstanceOf(TestModel2::class, $test);
                $this->assertSame($id, $test->id);
            },
            ['1234', 'TestModel2:1234'],
        );

        $this->expectException(AbortException::class);

        Caller::invoke(
            $this->context,
            function (int $id, #[FindDynamic] TestModel|TestModel2 $test) {
                $this->assertTrue(false, "Should not call this section!");
            },
            ['404', 'TestModel:404'],
        );
    }

    public function test_find_dynamic_with_null_value()
    {
        Caller::invoke(
            $this->context,
            function (#[FindDynamic] null|TestModel|TestModel2 $test) {
                $this->assertNull($test);
            },
            [null],
        );

        $this->expectException(AbortException::class);

        Caller::invoke(
            $this->context,
            function (#[FindDynamic] TestModel|TestModel2 $test) {
                $this->assertTrue(false, "Should not call this section!");
            },
            [null],
        );
    }

    public function test_find_dynamic_with_not_exists_value()
    {
        Caller::invoke(
            $this->context,
            function (#[FindDynamic(nullOnFail: true)] null|TestModel|TestModel2 $test) {
                $this->assertNull($test);
            },
            ['TestModel:404'],
        );

        $this->expectException(AbortException::class);

        Caller::invoke(
            $this->context,
            function (#[FindDynamic] null|TestModel|TestModel2 $test) {
                $this->assertTrue(false, "Should not call this section!");
            },
            ['TestModel:404'],
        );
    }

}

class TestModel extends Model
{
    protected $fillable = [
        'id',
        'foo',
        'bar',
    ];
}

class TestModel2 extends Model
{
    protected $fillable = [
        'id',
        'foo',
        'bar',
    ];
}

class TestRepo1
{
    public function findOr($id, $default)
    {
        return $id == '404' ? value($default) : new TestModel(
            [
                'id' => $id,
            ],
        );
    }
}

class TestRepo2
{
    public function findById($id)
    {
        return $id == '404' ? null : new TestModel(
            [
                'id' => $id,
            ],
        );
    }
}

class TestRepo3
{
    public function find($id)
    {
        return $id == '404' ? null : new TestModel(
            [
                'id' => $id,
            ],
        );
    }
}
