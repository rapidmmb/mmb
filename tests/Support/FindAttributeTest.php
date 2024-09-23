<?php

namespace Mmb\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Db\Attributes\FindBy;
use Mmb\Support\Db\Attributes\FindById;
use Mmb\Support\Db\Attributes\FindByRepo;
use Mmb\Support\Db\FinderFactory;
use Mmb\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FindAttributeTest extends TestCase
{

    public function runWithPov($callback)
    {
        return pov()->update(Update::make([]))
            ->bindSingleton(FinderFactory::class, new class extends FinderFactory
            {
                public function find(string $model, $id, $default = null)
                {
                    if ($id == '404')
                    {
                        return value($default);
                    }

                    return new TestModel([
                        'id' => $id,
                    ]);
                }

                public function findBy(string $model, string $key, $value, $default = null)
                {
                    if ($value == '404')
                    {
                        return value($default);
                    }

                    return new TestModel([
                        $key => $value,
                    ]);
                }
            })
            ->run($callback);
    }

    public function test_find_by_id()
    {
        $this->runWithPov(function ()
        {
            Caller::invoke(
                function(int $id, #[FindById] TestModel $test)
                {
                    $this->assertSame($id, $test->id);
                },
                [1234, 1234]
            );

            $this->expectException(HttpException::class);

            Caller::invoke(
                function(int $id, #[FindById] TestModel $test)
                {
                    $this->assertTrue(false, "Should not call this section!");
                },
                ['404', '404']
            );
        });
    }

    public function test_find_by()
    {
        $this->runWithPov(function ()
        {
            Caller::invoke(
                function($value, #[FindBy('foo')] TestModel $test)
                {
                    $this->assertSame($value, $test->foo);
                },
                ['bar', 'bar']
            );

            $this->expectException(HttpException::class);

            Caller::invoke(
                function(int $id, #[FindById] TestModel $test)
                {
                    $this->assertTrue(false, "Should not call this section!");
                },
                ['404', '404']
            );
        });
    }

    public function test_find_by_repo1()
    {
        $this->runWithPov(function ()
        {
            Caller::invoke(
                function($id, #[FindByRepo(TestRepo1::class)] TestModel $test)
                {
                    $this->assertSame($id, $test->id);
                },
                [1234, 1234]
            );

            $this->expectException(HttpException::class);

            Caller::invoke(
                function(int $id, #[FindByRepo(TestRepo1::class)] TestModel $test)
                {
                    $this->assertTrue(false, "Should not call this section!");
                },
                ['404', '404']
            );
        });
    }

    public function test_find_by_repo2()
    {
        $this->runWithPov(function ()
        {
            Caller::invoke(
                function($id, #[FindByRepo(TestRepo2::class)] TestModel $test)
                {
                    $this->assertSame($id, $test->id);
                },
                [1234, 1234]
            );

            $this->expectException(HttpException::class);

            Caller::invoke(
                function(int $id, #[FindByRepo(TestRepo2::class)] TestModel $test)
                {
                    $this->assertTrue(false, "Should not call this section!");
                },
                ['404', '404']
            );
        });
    }

    public function test_find_by_repo3()
    {
        $this->runWithPov(function ()
        {
            Caller::invoke(
                function($id, #[FindByRepo(TestRepo3::class)] TestModel $test)
                {
                    $this->assertSame($id, $test->id);
                },
                [1234, 1234]
            );

            $this->expectException(HttpException::class);

            Caller::invoke(
                function(int $id, #[FindByRepo(TestRepo3::class)] TestModel $test)
                {
                    $this->assertTrue(false, "Should not call this section!");
                },
                ['404', '404']
            );
        });
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

class TestRepo1
{
    public function findOr($id, $default)
    {
        return $id == '404' ? value($default) : new TestModel([
            'id' => $id,
        ]);
    }
}

class TestRepo2
{
    public function findById($id)
    {
        return $id == '404' ? null : new TestModel([
            'id' => $id,
        ]);
    }
}

class TestRepo3
{
    public function find($id)
    {
        return $id == '404' ? null : new TestModel([
            'id' => $id,
        ]);
    }
}
