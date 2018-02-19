<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use DataTypes\Url;
use MichaelHall\PageFetcher\FakePageFetcher;
use MichaelHall\PageFetcher\Interfaces\PageFetcherResponseInterface;
use MichaelHall\PageFetcher\PageFetcherResponse;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Modifiers;
use PHPUnit\Framework\TestCase;

/**
 * Test TestCase class.
 */
class TestCaseTest extends TestCase
{
    /**
     * Test empty test case.
     */
    public function testEmptyTestCase()
    {
        $testCase = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost'));

        self::assertSame([], $testCase->getAsserts());
    }

    /**
     * Test test case with asserts.
     */
    public function testWithAsserts()
    {
        $assert1 = new AssertContains('Foo', new Modifiers());
        $assert2 = new AssertContains('Bar', new Modifiers(Modifiers::NOT));
        $assert3 = new AssertEmpty(new Modifiers());

        $testCase = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost'));
        $testCase->addAssert($assert1);
        $testCase->addAssert($assert2);
        $testCase->addAssert($assert3);

        self::assertSame([$assert1, $assert2, $assert3], $testCase->getAsserts());
    }

    /**
     * Test getUrl method.
     */
    public function testGetUrl()
    {
        $url = Url::parse('http://localhost/foo/bar');
        $testCase = new \MichaelHall\Webunit\TestCase($url);

        self::assertSame($url, $testCase->getUrl());
    }

    /**
     * Test run successful test.
     */
    public function testRunSuccessfulTest()
    {
        $assert1 = new AssertContains('Foo', new Modifiers());
        $assert2 = new AssertContains('Bar', new Modifiers(Modifiers::NOT));
        $assert3 = new AssertEquals('Foo Baz', new Modifiers());
        $assert4 = new AssertEmpty(new Modifiers(Modifiers::NOT));

        $testCase = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost'));
        $testCase->addAssert($assert1);
        $testCase->addAssert($assert2);
        $testCase->addAssert($assert3);
        $testCase->addAssert($assert4);

        $pageFetcher = new FakePageFetcher();
        $pageFetcher->setResponseHandler(function (): PageFetcherResponseInterface {
            return new PageFetcherResponse(200, 'Foo Baz');
        });

        $result = $testCase->run($pageFetcher);

        self::assertSame($testCase, $result->getTestCase());
        self::assertTrue($result->isSuccess());
        self::assertNull($result->getFailedAssertResult());
    }

    /**
     * Test run failed test.
     */
    public function testRunFailedTest()
    {
        $assert1 = new AssertContains('Foo', new Modifiers());
        $assert2 = new AssertContains('Bar', new Modifiers(Modifiers::NOT));
        $assert3 = new AssertEquals('Baz', new Modifiers());
        $assert4 = new AssertEmpty(new Modifiers(Modifiers::NOT));

        $testCase = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost'));
        $testCase->addAssert($assert1);
        $testCase->addAssert($assert2);
        $testCase->addAssert($assert3);
        $testCase->addAssert($assert4);

        $pageFetcher = new FakePageFetcher();
        $pageFetcher->setResponseHandler(function (): PageFetcherResponseInterface {
            return new PageFetcherResponse(200, 'Foo Baz Bar');
        });

        $result = $testCase->run($pageFetcher);

        self::assertSame($testCase, $result->getTestCase());
        self::assertFalse($result->isSuccess());
        self::assertFalse($result->getFailedAssertResult()->isSuccess());
        self::assertSame($assert2, $result->getFailedAssertResult()->getAssert());
        self::assertSame('Content "Foo Baz Bar" contains "Bar"', $result->getFailedAssertResult()->getError());
    }
}
