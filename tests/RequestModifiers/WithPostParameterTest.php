<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\RequestModifiers;

use DataTypes\Net\Url;
use MichaelHall\HttpClient\HttpClientRequest;
use MichaelHall\Webunit\Exceptions\EmptyParameterNameException;
use MichaelHall\Webunit\RequestModifiers\WithPostParameter;
use PHPUnit\Framework\TestCase;

/**
 * Test WithPostParameters class.
 */
class WithPostParameterTest extends TestCase
{
    /**
     * Test getParameterName method.
     */
    public function testGetParameterName()
    {
        $withPostParameter = new WithPostParameter('Foo', 'Bar');

        self::assertSame('Foo', $withPostParameter->getParameterName());
    }

    /**
     * Test getParameterValue method.
     */
    public function testGetParameterValue()
    {
        $withPostParameter = new WithPostParameter('Foo', 'Bar');

        self::assertSame('Bar', $withPostParameter->getParameterValue());
    }

    /**
     * Test modifyRequest method.
     */
    public function testModifyRequest()
    {
        $request = new HttpClientRequest(Url::parse('https://example.com/'));

        $withPostParameter = new WithPostParameter('Foo', 'Bar');
        $withPostParameter->modifyRequest($request);

        self::assertSame(['Foo' => 'Bar'], $request->getPostFields());
    }

    /**
     * Test create request modifier with an empty parameter name.
     */
    public function testWithEmptyParameterName()
    {
        self::expectException(EmptyParameterNameException::class);
        self::expectExceptionMessage('POST parameter name is empty');

        new WithPostParameter('', 'Bar');
    }
}
