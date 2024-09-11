<?php

namespace Mmb\Tests\POV;

use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Memory\StepHandler;
use Mmb\Core\Updates\Update;
use Mmb\Support\Db\HasFinder;
use Mmb\Support\Db\ModelFinder;
use Mmb\Support\Pov\POV;
use Mmb\Support\Step\Stepping;
use Mmb\Tests\TestCase;

class POVTest extends TestCase
{

    public function test_changing_the_update_and_revert_it()
    {
        $original = new Update([]);
        $fake = new Update([]);

        app()->singleton(Update::class, fn() => $original);
        $this->assertSame($original, app(Update::class));

        pov()->update($fake)->run(
            fn () => $this->assertSame($fake, app(Update::class)),
        );

        $this->assertSame($original, app(Update::class));
    }

    public function test_changing_the_user_and_revert_it()
    {
        $original = new UserTest();
        $fake = new UserTest();

        ModelFinder::storeCurrent($original);

        pov()->user($fake)->run(
            fn() => $this->assertSame($fake, UserTest::current())
        );

        $this->assertSame($original, UserTest::current());
        $this->assertSame(true, $fake->isSaved);
    }

}

class UserTest extends Model implements Stepping
{
    use HasFinder;

    public function getStep() : ?StepHandler
    {
        return null;
    }

    public function setStep(?StepHandler $stepHandler)
    {
    }

    public bool $isSaved = false;

    public function save(array $options = [])
    {
        $this->isSaved = true;
    }

}
