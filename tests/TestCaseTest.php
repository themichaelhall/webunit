<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use DataTypes\FilePath;
use DataTypes\Url;
use MichaelHall\PageFetcher\FakePageFetcher;
use MichaelHall\PageFetcher\Interfaces\PageFetcherResponseInterface;
use MichaelHall\PageFetcher\PageFetcherResponse;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Assertions\AssertStatusCode;
use MichaelHall\Webunit\Assertions\DefaultAssert;
use MichaelHall\Webunit\Interfaces\AssertResultInterface;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Location\FileLocation;
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
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $testCase = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost'));

        self::assertSame(1, count($testCase->getAsserts()));
        self::assertSame(DefaultAssert::class, get_class($testCase->getAsserts()[0]));
    }

    /**
     * Test test case with asserts.
     */
    public function testWithAsserts()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $assert1 = new AssertContains($location, 'Foo', new Modifiers());
        $assert2 = new AssertContains($location, 'Bar', new Modifiers(ModifiersInterface::NOT));
        $assert3 = new AssertEmpty($location, new Modifiers());

        $testCase = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost'));
        $testCase->addAssert($assert1);
        $testCase->addAssert($assert2);
        $testCase->addAssert($assert3);

        self::assertSame(4, count($testCase->getAsserts()));
        self::assertSame(DefaultAssert::class, get_class($testCase->getAsserts()[0]));
        self::assertSame($assert1, $testCase->getAsserts()[1]);
        self::assertSame($assert2, $testCase->getAsserts()[2]);
        self::assertSame($assert3, $testCase->getAsserts()[3]);
    }

    /**
     * Test test case with status code assert (should remove default assert).
     */
    public function testStatusCodeAssertRemovesDefaultAssert()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $assert1 = new AssertContains($location, 'Foo', new Modifiers());
        $assert2 = new AssertStatusCode($location, 404, new Modifiers());
        $assert3 = new AssertEmpty($location, new Modifiers());

        $testCase = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost'));
        $testCase->addAssert($assert1);
        $testCase->addAssert($assert2);
        $testCase->addAssert($assert3);

        self::assertSame(3, count($testCase->getAsserts()));
        self::assertSame($assert1, $testCase->getAsserts()[0]);
        self::assertSame($assert2, $testCase->getAsserts()[1]);
        self::assertSame($assert3, $testCase->getAsserts()[2]);
    }

    /**
     * Test getUrl method.
     */
    public function testGetUrl()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $url = Url::parse('http://localhost/foo/bar');
        $testCase = new \MichaelHall\Webunit\TestCase($location, $url);

        self::assertSame($url, $testCase->getUrl());
    }

    /**
     * Test run successful test.
     */
    public function testRunSuccessfulTest()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $assert1 = new AssertContains($location, 'Foo', new Modifiers());
        $assert2 = new AssertContains($location, 'Bar', new Modifiers(ModifiersInterface::NOT));
        $assert3 = new AssertEquals($location, 'Foo Baz', new Modifiers(ModifiersInterface::CASE_INSENSITIVE));
        $assert4 = new AssertEmpty($location, new Modifiers(ModifiersInterface::NOT));
        $assert5 = new AssertStatusCode($location, 200, new Modifiers());

        $testCase = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost'));
        $testCase->addAssert($assert1);
        $testCase->addAssert($assert2);
        $testCase->addAssert($assert3);
        $testCase->addAssert($assert4);
        $testCase->addAssert($assert5);

        $pageFetcher = new FakePageFetcher();
        $pageFetcher->setResponseHandler(function (): PageFetcherResponseInterface {
            return new PageFetcherResponse(200, 'Foo baz');
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
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $assert1 = new AssertContains($location, 'Foo', new Modifiers());
        $assert2 = new AssertContains($location, 'Bar', new Modifiers(ModifiersInterface::NOT));
        $assert3 = new AssertEquals($location, 'Baz', new Modifiers());
        $assert4 = new AssertEmpty($location, new Modifiers(ModifiersInterface::NOT));
        $assert5 = new AssertStatusCode($location, 200, new Modifiers());

        $testCase = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost'));
        $testCase->addAssert($assert1);
        $testCase->addAssert($assert2);
        $testCase->addAssert($assert3);
        $testCase->addAssert($assert4);
        $testCase->addAssert($assert5);

        $pageFetcher = new FakePageFetcher();
        $pageFetcher->setResponseHandler(function (): PageFetcherResponseInterface {
            return new PageFetcherResponse(404, 'Foo Baz Bar');
        });

        $result = $testCase->run($pageFetcher);

        self::assertSame($testCase, $result->getTestCase());
        self::assertFalse($result->isSuccess());
        self::assertFalse($result->getFailedAssertResult()->isSuccess());
        self::assertSame($assert2, $result->getFailedAssertResult()->getAssert());
        self::assertSame('Content "Foo Baz Bar" contains "Bar"', $result->getFailedAssertResult()->getError());
    }

    /**
     * Test run with callback.
     */
    public function testRunWithCallback()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $assert1 = new AssertContains($location, 'Foo', new Modifiers());
        $assert2 = new AssertContains($location, 'Bar', new Modifiers(ModifiersInterface::NOT));
        $assert3 = new AssertContains($location, 'Baz', new Modifiers());

        $testCase = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost'));
        $testCase->addAssert($assert1);
        $testCase->addAssert($assert2);
        $testCase->addAssert($assert3);

        $pageFetcher = new FakePageFetcher();
        $pageFetcher->setResponseHandler(function (): PageFetcherResponseInterface {
            return new PageFetcherResponse(200, 'Foo baz');
        });

        /** @var AssertResultInterface[] $callbackResult */
        $callbackResult = [];
        $result = $testCase->run($pageFetcher, function (AssertResultInterface $assertResult) use (&$callbackResult) {
            $callbackResult[] = $assertResult;
        });

        self::assertSame($testCase, $result->getTestCase());
        self::assertFalse($result->isSuccess());
        self::assertSame('Content "Foo baz" does not contain "Baz"', $result->getFailedAssertResult()->getError());

        self::assertSame(4, count($callbackResult));
        self::assertTrue($callbackResult[0]->isSuccess());
        self::assertSame('', $callbackResult[0]->getError());
        self::assertInstanceOf(DefaultAssert::class, $callbackResult[0]->getAssert());
        self::assertTrue($callbackResult[1]->isSuccess());
        self::assertSame('', $callbackResult[1]->getError());
        self::assertSame($assert1, $callbackResult[1]->getAssert());
        self::assertTrue($callbackResult[2]->isSuccess());
        self::assertSame('', $callbackResult[2]->getError());
        self::assertSame($assert2, $callbackResult[2]->getAssert());
        self::assertFalse($callbackResult[3]->isSuccess());
        self::assertSame('Content "Foo baz" does not contain "Baz"', $callbackResult[3]->getError());
        self::assertSame($assert3, $callbackResult[3]->getAssert());
    }
}
