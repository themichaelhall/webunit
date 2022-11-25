<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\RequestModifiers;

use DataTypes\Net\Url;
use DataTypes\System\FilePath;
use MichaelHall\HttpClient\HttpClientRequest;
use MichaelHall\Webunit\RequestModifiers\WithPostFile;
use MichaelHall\Webunit\RequestModifiers\WithPostParameter;
use MichaelHall\Webunit\RequestModifiers\WithRawContent;
use PHPUnit\Framework\TestCase;

/**
 * Test WithRawContent class.
 */
class WithRawContentTest extends TestCase
{
    /**
     * Test getContent method.
     */
    public function testGetContent()
    {
        $withRawContent = new WithRawContent('Foo');

        self::assertSame('Foo', $withRawContent->getContent());
    }

    /**
     * Test modifyRequest method.
     */
    public function testModifyRequest()
    {
        $request = new HttpClientRequest(Url::parse('https://example.com/'));

        $withRawContent = new WithRawContent('Foo');
        $withRawContent->modifyRequest($request);

        self::assertSame('Foo', $request->getRawContent());
    }

    /**
     * Test isCompatibleWith method.
     */
    public function testIsCompatibleWith()
    {
        $withRawContent = new WithRawContent('Foo');

        self::assertFalse($withRawContent->isCompatibleWith(new WithPostFile('Bar', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt'))));
        self::assertFalse($withRawContent->isCompatibleWith(new WithPostParameter('Bar', 'Baz')));
        self::assertTrue($withRawContent->isCompatibleWith(new WithRawContent('{"Baz": true}')));
    }
}
