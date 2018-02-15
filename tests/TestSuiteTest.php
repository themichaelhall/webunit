<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use DataTypes\Url;
use MichaelHall\PageFetcher\FakePageFetcher;
use MichaelHall\PageFetcher\Interfaces\PageFetcherInterface;
use MichaelHall\PageFetcher\Interfaces\PageFetcherRequestInterface;
use MichaelHall\PageFetcher\Interfaces\PageFetcherResponseInterface;
use MichaelHall\PageFetcher\PageFetcherResponse;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\TestSuite;
use PHPUnit\Framework\TestCase;

/**
 * Test TestSuite class.
 */
class TestSuiteTest extends TestCase
{
    /**
     * Test empty test suite.
     */
    public function testEmptyTestSuite()
    {
        $testSuite = new TestSuite();

        self::assertSame([], $testSuite->getTestCases());
    }

    /**
     * Test test suite with test cases.
     */
    public function testWithTestCases()
    {
        $testCase1 = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost'));
        $testCase2 = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost/foo'));

        $testSuite = new TestSuite();
        $testSuite->addTestCase($testCase1);
        $testSuite->addTestCase($testCase2);

        self::assertSame([$testCase1, $testCase2], $testSuite->getTestCases());
    }

    /**
     * Test run empty test.
     */
    public function testRunEmptyTest()
    {
        $testSuite = new TestSuite();

        $result = $testSuite->run($this->pageFetcher);

        self::assertTrue($result->isSuccess());
        self::assertSame($testSuite, $result->getTestSuite());
        self::assertSame([], $result->getTestCaseResults());
        self::assertSame([], $result->getFailedTestCaseResults());
    }

    /**
     * Test run successful tests.
     */
    public function testRunSuccessfulTests()
    {
        $testCase1 = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost/foo'));
        $testCase1->addAssert(new AssertEquals('This is Foo page.', new Modifiers()));
        $testCase1->addAssert(new AssertContains('Bar', new Modifiers(Modifiers::NOT)));

        $testCase2 = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost/bar'));
        $testCase2->addAssert(new AssertContains('Foo', new Modifiers(Modifiers::NOT)));
        $testCase2->addAssert(new AssertContains('Bar', new Modifiers()));

        $testSuite = new TestSuite();
        $testSuite->addTestCase($testCase1);
        $testSuite->addTestCase($testCase2);

        $result = $testSuite->run($this->pageFetcher);

        self::assertTrue($result->isSuccess());
        self::assertSame($testSuite, $result->getTestSuite());

        self::assertSame(2, count($result->getTestCaseResults()));
        self::assertSame($testCase1, $result->getTestCaseResults()[0]->getTestCase());
        self::assertTrue($result->getTestCaseResults()[0]->isSuccess());
        self::assertSame($testCase2, $result->getTestCaseResults()[1]->getTestCase());
        self::assertTrue($result->getTestCaseResults()[1]->isSuccess());

        self::assertSame([], $result->getFailedTestCaseResults());
    }

    /**
     * Test run unsuccessful tests.
     */
    public function testRunUnSuccessfulTests()
    {
        $testCase1 = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost/foo'));
        $testCase1->addAssert(new AssertContains('Foo', new Modifiers(Modifiers::NOT)));
        $testCase1->addAssert(new AssertContains('Bar', new Modifiers()));

        $testCase2 = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost/bar'));
        $testCase2->addAssert(new AssertContains('Foo', new Modifiers()));
        $testCase2->addAssert(new AssertContains('Bar', new Modifiers(Modifiers::NOT)));

        $testCase3 = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost/baz'));
        $testCase3->addAssert(new AssertContains('Baz', new Modifiers()));

        $testSuite = new TestSuite();
        $testSuite->addTestCase($testCase1);
        $testSuite->addTestCase($testCase2);
        $testSuite->addTestCase($testCase3);

        $result = $testSuite->run($this->pageFetcher);

        self::assertFalse($result->isSuccess());
        self::assertSame($testSuite, $result->getTestSuite());

        self::assertSame(3, count($result->getTestCaseResults()));
        self::assertSame($testCase1, $result->getTestCaseResults()[0]->getTestCase());
        self::assertFalse($result->getTestCaseResults()[0]->isSuccess());
        self::assertFalse($result->getTestCaseResults()[0]->getFailedAssertResult()->isSuccess());
        self::assertSame('Content "This is Foo page." contains "Foo"', $result->getTestCaseResults()[0]->getFailedAssertResult()->getError());
        self::assertSame($testCase2, $result->getTestCaseResults()[1]->getTestCase());
        self::assertFalse($result->getTestCaseResults()[1]->isSuccess());
        self::assertFalse($result->getTestCaseResults()[1]->getFailedAssertResult()->isSuccess());
        self::assertSame('Content "This is Bar page." does not contain "Foo"', $result->getTestCaseResults()[1]->getFailedAssertResult()->getError());
        self::assertSame($testCase3, $result->getTestCaseResults()[2]->getTestCase());
        self::assertTrue($result->getTestCaseResults()[2]->isSuccess());

        self::assertSame(2, count($result->getFailedTestCaseResults()));
        self::assertSame($testCase1, $result->getFailedTestCaseResults()[0]->getTestCase());
        self::assertFalse($result->getFailedTestCaseResults()[0]->isSuccess());
        self::assertFalse($result->getFailedTestCaseResults()[0]->getFailedAssertResult()->isSuccess());
        self::assertSame('Content "This is Foo page." contains "Foo"', $result->getFailedTestCaseResults()[0]->getFailedAssertResult()->getError());
        self::assertSame($testCase2, $result->getFailedTestCaseResults()[1]->getTestCase());
        self::assertFalse($result->getFailedTestCaseResults()[1]->isSuccess());
        self::assertFalse($result->getFailedTestCaseResults()[1]->getFailedAssertResult()->isSuccess());
        self::assertSame('Content "This is Bar page." does not contain "Foo"', $result->getFailedTestCaseResults()[1]->getFailedAssertResult()->getError());
    }

    /**
     * Set up.
     */
    public function setUp()
    {
        $this->pageFetcher = new FakePageFetcher();
        $this->pageFetcher->setResponseHandler(function (PageFetcherRequestInterface $request): PageFetcherResponseInterface {
            switch ($request->getUrl()->getPath()) {
                case '/foo':
                    return new PageFetcherResponse(200, 'This is Foo page.');
                case '/bar':
                    return new PageFetcherResponse(200, 'This is Bar page.');
                case '/baz':
                    return new PageFetcherResponse(200, 'This is Baz page.');
            }

            return new PageFetcherResponse(404);
        });
    }

    /**
     * Tear down.
     */
    public function tearDown()
    {
        $this->pageFetcher = null;
    }

    /**
     * @var PageFetcherInterface My fake page fetcher.
     */
    private $pageFetcher;
}
