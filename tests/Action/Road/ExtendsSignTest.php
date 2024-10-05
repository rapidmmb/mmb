<?php

namespace Mmb\Tests\Action\Road;

use Illuminate\Support\Str;
use Mmb\Action\Road\Road;
use Mmb\Action\Road\Sign as Base;
use Mmb\Action\Road\Station as BaseStation;
use Mmb\Core\Updates\Update;
use Mmb\Tests\TestCase;

class ExtendsSignTest extends TestCase
{

    public function test_define_method()
    {
        $sign = new class(new Road()) extends Sign
        {
            protected function boot()
            {
                parent::boot();
                $this->defineMethod('test', fn () => 'Foo');
                $this->defineMethod('test2', fn ($arg) => $arg);
            }
        };

        $this->assertSame('Foo', $sign->test());
        $this->assertSame('Bar', $sign->test2('Bar'));

        $this->expectException(\BadMethodCallException::class);
        $sign->undefinedMethod();
    }

    public function test_define_label()
    {
        $road = new Road;
        $sign = new class($road) extends Sign
        {
            protected function boot()
            {
                parent::boot();

                $this->defineLabel('testLabel');
            }

            protected function onTestLabel()
            {
                return 'Foo';
            }

            protected function onTestLabelUsing($string)
            {
                return "#$string#";
            }

            public function getTestLabel(Station $station)
            {
                return $this->getDefinedLabel($station, 'testLabel');
            }
        };
        $station = new Station($road, $sign);

        $this->assertSame('#Foo#', $sign->getTestLabel($station));

        $sign->testLabel(fn () => 'Bar');
        $this->assertSame('#Bar#', $sign->getTestLabel($station));

        $sign->testLabel(fn () => 'SomeText');
        $this->assertSame('#SomeText#', $sign->getTestLabel($station));

        $sign->testLabelUsing(fn ($str) => Str::kebab($str));
        $this->assertSame('#some-text#', $sign->getTestLabel($station));

        $sign->testLabelPrefix('<');
        $sign->testLabelSuffix(fn () => '>');
        $this->assertSame('#<some-text>#', $sign->getTestLabel($station));
    }

}

class Sign extends Base
{
    public function createStation(Update $update) : BaseStation
    {
        return value(null); // Just ignore errors
    }
}

class Station extends BaseStation
{

}
