<?php

namespace Mmb\Tests\POV;

use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Memory\Step;
use Mmb\Action\Memory\StepHandler;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Db\HasFinder;
use Mmb\Support\Db\ModelFinderDeprecated;
use Mmb\Support\Pov\POVFactory;
use Mmb\Support\Step\Contracts\ConvertableToStepper;
use Mmb\Support\Step\Contracts\Stepper;
use Mmb\Tests\TestCase;

class POVTest extends TestCase
{

    public function test_changing_the_update_and_revert_it()
    {
        $original = $this->context;
        $original->put('foo', 'bar');

        pov()->put('foo', 'not bar')->run(
            function (Context $context) use ($original) {
                $this->assertNotSame($original, $context);
                $this->assertSame('not bar', $context->foo);
            },
        );
    }

    public function test_changing_the_user_and_revert_it()
    {
        $fake = new UserTest(['id' => 100]);

        pov()->user($fake)->run(
            fn(Context $context) => $this->assertSame($fake, $context->stepper)
        );
    }

    public function test_pov_with_start_and_end_functions()
    {
        $fake = new Update([]);

        $pov = pov()->update($fake);
        $context = $pov->toContext();

        $this->assertSame($fake, $context->update);
    }

    public function test_changing_user_changed_the_chat_id()
    {
        $fake = new UserTest(['id' => 5678]);

        pov()->user($fake)->run(
            fn(Context $context) => $this->assertSame($fake->id, $context->update->getChat()->id)
        );
    }

    public function test_changing_the_user_by_convertable()
    {
        $fake = new UserTest(['id' => 100]);
        $fakeConvertable = new class($fake) implements ConvertableToStepper
        {
            public function __construct(public $fake)
            {
            }

            public function toStepper() : Stepper
            {
                return $this->fake;
            }
        };

        pov()->user($fakeConvertable)->run(
            fn(Context $context) => $this->assertSame($fake, $context->stepper)
        );
    }

}

class UserTest extends Model implements Stepper
{
    use HasFinder;

    protected $fillable = ['id'];

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
