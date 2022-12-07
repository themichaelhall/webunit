<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\RequestModifiers;

use DataTypes\Net\Url;
use DataTypes\System\FilePath;
use MichaelHall\HttpClient\HttpClientRequest;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
use MichaelHall\Webunit\RequestModifiers\WithHeader;
use MichaelHall\Webunit\RequestModifiers\WithPostFile;
use MichaelHall\Webunit\RequestModifiers\WithPostParameter;
use MichaelHall\Webunit\RequestModifiers\WithRawContent;
use PHPUnit\Framework\TestCase;

/**
 * Test WithHeaderTest class.
 */
class WithHeaderTest extends TestCase
{
    /**
     * Test getHeaderName method.
     */
    public function testGetHeaderName()
    {
        $withHeader = new WithHeader('Foo', 'Bar');

        self::assertSame('Foo', $withHeader->getHeaderName());
    }

    /**
     * Test getHeaderValue method.
     */
    public function testGetHeaderValue()
    {
        $withHeader = new WithHeader('Foo', 'Bar');

        self::assertSame('Bar', $withHeader->getHeaderValue());
    }

    /**
     * Test modifyRequest method.
     */
    public function testModifyRequest()
    {
        $request = new HttpClientRequest(Url::parse('https://example.com/'));

        $withHeader = new WithHeader('Foo', 'Bar');
        $withHeader->modifyRequest($request);

        self::assertSame(['Foo: Bar'], $request->getHeaders());
    }

    /**
     * Test isCompatibleWith method.
     */
    public function testIsCompatibleWith()
    {
        $withHeader = new WithHeader('Foo', 'Bar');

        self::assertTrue($withHeader->isCompatibleWith(new WithPostFile('Bar', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt'))));
        self::assertTrue($withHeader->isCompatibleWith(new WithPostParameter('Bar', 'Baz')));
        self::assertTrue($withHeader->isCompatibleWith(new WithRawContent('{"Baz": true}')));
        self::assertTrue($withHeader->isCompatibleWith(new WithHeader('Foo', 'Bar')));
    }

    /**
     * TestIsAllowedForMethod method.
     */
    public function testIsAllowedForMethod()
    {
        $withHeader = new WithHeader('Foo', 'Bar');

        self::assertTrue($withHeader->isAllowedForMethod(TestCaseInterface::METHOD_GET));
        self::assertTrue($withHeader->isAllowedForMethod(TestCaseInterface::METHOD_POST));
        self::assertTrue($withHeader->isAllowedForMethod(TestCaseInterface::METHOD_PUT));
        self::assertTrue($withHeader->isAllowedForMethod(TestCaseInterface::METHOD_PATCH));
        self::assertTrue($withHeader->isAllowedForMethod(TestCaseInterface::METHOD_DELETE));
    }
}
