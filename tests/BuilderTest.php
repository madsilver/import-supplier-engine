<?php

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    private $supplier;

    public function setUp() {
        $this->supplier = array(
            "name" => "Test",
            "description" => "Description test",
            "street" => "Street test",
            "postal_code" => "00000-000",
            "city" => "City Test",
            "state" => "State Test",
            "country" => "Country test",
            "phone" => "(11) 2222-4444",
            "e-mail" => "test@test.com",
            "cnpj" => "1234567899999"
        );
    }

    public function testValidPostaCode()
    {
        $builder = new \Silver\Builder(1);
        $payload = $builder->payload($this->supplier);
        $this->assertEquals($payload['company']['postal_code'], '00000000');
    }

    public function testValidPhone()
    {
        $builder = new \Silver\Builder(1);
        $payload = $builder->payload($this->supplier);
        $this->assertEquals($payload['company']['phone'], '1122224444');
    }

    public function testValidCnpj()
    {
        $builder = new \Silver\Builder(1);
        $payload = $builder->payload($this->supplier);
        $this->assertEquals($payload['company']['cnpj'], '01234567899999');
    }

}