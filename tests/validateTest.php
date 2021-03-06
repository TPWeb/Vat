<?php
use TPWeb\Vat\Vat;
use TPWeb\Vat\Validation\ValidatorExtensions;
use Illuminate\Validation\Factory;

class validateTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }
    
    public function testValidatorVatFalse() {
        $validator = new TPWeb\Vat\Validation\Validator;
        $this->assertFalse($validator->isVat("BE123456789"));
    }
    
    public function testValidatorVat() {
        $validator = new TPWeb\Vat\Validation\Validator;
        $this->assertTrue($validator->isVat("BE0653.855.818"));
    }
    
    public function testValidVat()
    {
        $validator = Mockery::mock('TPWeb\Vat\Validation\Validator');
        $extensions = new ValidatorExtensions($validator);

        $container = Mockery::mock('Illuminate\Container\Container');
        $translator = Mockery::mock('Illuminate\Contracts\Translation\Translator');

        $container->shouldReceive('make')->once()->with('TPWeb\Vat\Validation\ValidatorExtensions')->andReturn($extensions);
        $validator->shouldReceive('isVat')->once()->with('BE0653855818')->andReturn(true);

        $factory = new Factory($translator, $container);
        $factory->extend('vat', 'TPWeb\Vat\Validation\ValidatorExtensions@validateVat', ':attribute must be a valid VAT');
        $validator = $factory->make(['foo' => 'BE0653855818'], ['foo' => 'vat']);
        $this->assertTrue($validator->passes());
    }

    public function testValidVatFails()
    {
        $validator = Mockery::mock('TPWeb\Vat\Validation\Validator');
        $extensions = new ValidatorExtensions($validator);

        $container = Mockery::mock('Illuminate\Container\Container');
        $translator = Mockery::mock('Illuminate\Contracts\Translation\Translator');

        $container->shouldReceive('make')->once()->with('TPWeb\Vat\Validation\ValidatorExtensions')->andReturn($extensions);
        $validator->shouldReceive('isVat')->once()->with('BE0650005818')->andReturn(false);
        $translator->shouldReceive('trans')->once()->with('validation.custom')->andReturn('validation.custom');
        $translator->shouldReceive('trans')->once()->with('validation.custom.foo.vat')->andReturn('validation.custom.foo.vat');
        $translator->shouldReceive('trans')->once()->with('validation.vat')->andReturn('validation.vat');
        $translator->shouldReceive('trans')->once()->with('validation.attributes')->andReturn('validation.attributes');

        $factory = new Factory($translator, $container);
        $factory->extend('vat', 'TPWeb\Vat\Validation\ValidatorExtensions@validateVat', ':attribute must be a valid VAT');
        $validator = $factory->make(['foo' => 'BE0650005818'], ['foo' => 'vat']);
        $this->assertTrue($validator->fails());

        $messages = $validator->messages();
        $this->assertInstanceOf('Illuminate\Support\MessageBag', $messages);
        $this->assertEquals('foo must be a valid VAT', $messages->first('foo'));
    }
}
