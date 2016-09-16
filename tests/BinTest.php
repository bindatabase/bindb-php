<?php

use Riad\Bin;

require_once dirname(__DIR__) . '/vendor/autoload.php';

class BinTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->bin = new Bin();
        $this->expectedObj = (object) [
            'bin'          => 437776,
            'country_code' => 'MA',
            'vendor'       => 'VISA',
            'type'         => 'DEBIT',
            'level'        => 'CLASSIC',
            'is_prepaid'   => false,
            'issuer'       => 'ATTIJARIWAFA BANK'
        ];
    }

    public function testConstructorWithIncorrectToken ()
    {
        $this->expectException(InvalidArgumentException::class);
        new Bin(['fakeToken']);
        new Bin(true);
        new Bin(123456);
    }

    public function testConstructorWithCorrectToken ()
    {
        new Bin();
        new Bin('fakeToken');
    }

    public function testRawWithValidBin ()
    {
        $this->assertEquals($this->expectedObj, json_decode($this->bin->raw(437776)));
    }

    public function testRawWithInvalidBin ()
    {
        $this->assertEquals(true, json_decode($this->bin->raw('000000'))->error);
    }

    public function testGetWithValidBin ()
    {
        $this->assertEquals($this->expectedObj, $this->bin->get(437776));
        $this->assertEquals($this->expectedObj, $this->bin->search(437776));
        $this->assertEquals($this->expectedObj, $this->bin->lookup(437776));
    }

    public function testGetWithInvalidBin ()
    {
        $this->assertEquals(null, $this->bin->get('000000'));
    }

    public function testGetWithInvalidBinAndErrorMode ()
    {
        $this->expectException(Exception::class);
        $this->bin->error(true);
        $this->bin->get('000000');
    }

    public function testGetWithFields ()
    {
        $result = $this->bin->fields(['bin', 'issuer'])->get(437776);
        $expected = (object) [ 'bin' => 437776, 'issuer' => 'ATTIJARIWAFA BANK'];
        $this->assertEquals($expected, $result);
    }

    public function testQuery ()
    {
        $result1 = $this->bin->query('SELECT * FROM bins WHERE bin = 437776')->run();
        $result2 = $this->bin->query('SELECT bin,issuer FROM bins WHERE bin = ?')->run(437776);
        $result3 = $this->bin->query('SELECT bin,issuer FROM bins WHERE bin = ?')->run([437776]);
        $this->assertEquals($this->expectedObj, $result1);
        $this->assertEquals((object) [ 'bin' => 437776, 'issuer' => 'ATTIJARIWAFA BANK'], $result2);
        $this->assertEquals((object) [ 'bin' => 437776, 'issuer' => 'ATTIJARIWAFA BANK'], $result3);
    }

    public function testQueryWithoutParam ()
    {
        $result = $this->bin->query('SELECT * FROM bins WHERE bin = ?')->run();
        $this->assertEquals(null, $result);
    }

    public function testQueryWithoutParamAndErrorMode ()
    {
        $this->bin->error(true);
        $this->expectException(RuntimeException::class);
        $this->bin->query('SELECT * FROM bins WHERE bin = ?')->run();
    }

    public function testRunWithoutQuery ()
    {
        $result1 = $this->bin->run();
        $result2 = $this->bin->run(437776);
        $this->assertEquals(null, $result1);
        $this->assertEquals(null, $result2);
    }

    public function testRunWithoutQueryAndErrorMode ()
    {
        $this->bin->error(true);
        $this->expectException(LogicException::class);
        $this->bin->run();
        $this->bin->run(437776);
    }
}