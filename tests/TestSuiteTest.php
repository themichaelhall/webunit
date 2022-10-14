<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use DataTypes\Net\Url;
use DataTypes\System\FilePath;
use MichaelHall\HttpClient\HttpClient;
use MichaelHall\HttpClient\HttpClientInterface;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Assertions\AssertHeader;
use MichaelHall\Webunit\Assertions\DefaultAssert;
use MichaelHall\Webunit\Interfaces\AssertResultInterface;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\Tests\Helpers\RequestHandlers\TestRequestHandler;
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
     * Test suite with test cases.
     */
    public function testWithTestCases()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $testCase1 = new \MichaelHall\Webunit\TestCase($location, TestCaseInterface::METHOD_GET, Url::parse('http://localhost'));
        $testCase2 = new \MichaelHall\Webunit\TestCase($location, TestCaseInterface::METHOD_GET, Url::parse('http://localhost/foo'));

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

        $result = $testSuite->run($this->httpClient);

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
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $testCase1 = new \MichaelHall\Webunit\TestCase($location, TestCaseInterface::METHOD_GET, Url::parse('http://localhost/foo'));
        $testCase1->addAssert(new AssertEquals($location, 'This is Foo page.', new Modifiers()));
        $testCase1->addAssert(new AssertContains($location, 'Bar', new Modifiers(ModifiersInterface::NOT)));
        $testCase1->addAssert(new AssertEmpty($location, new Modifiers(ModifiersInterface::NOT)));
        $testCase1->addAssert(new AssertHeader($location, 'x-f[o]+:X-BAR', new Modifiers(ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE)));

        $testCase2 = new \MichaelHall\Webunit\TestCase($location, TestCaseInterface::METHOD_GET, Url::parse('http://localhost/bar'));
        $testCase2->addAssert(new AssertContains($location, 'Foo', new Modifiers(ModifiersInterface::NOT)));
        $testCase2->addAssert(new AssertContains($location, 'bar', new Modifiers(ModifiersInterface::CASE_INSENSITIVE)));
        $testCase2->addAssert(new AssertEmpty($location, new Modifiers(ModifiersInterface::NOT)));

        $testSuite = new TestSuite();
        $testSuite->addTestCase($testCase1);
        $testSuite->addTestCase($testCase2);

        $result = $testSuite->run($this->httpClient);

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
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $testCase1 = new \MichaelHall\Webunit\TestCase($location, TestCaseInterface::METHOD_GET, Url::parse('http://localhost/foo'));
        $testCase1->addAssert(new AssertContains($location, 'Foo', new Modifiers(ModifiersInterface::NOT)));
        $testCase1->addAssert(new AssertContains($location, 'Bar', new Modifiers()));

        $testCase2 = new \MichaelHall\Webunit\TestCase($location, TestCaseInterface::METHOD_GET, Url::parse('http://localhost/bar'));
        $testCase2->addAssert(new AssertContains($location, 'Foo', new Modifiers()));
        $testCase2->addAssert(new AssertContains($location, 'Bar', new Modifiers(ModifiersInterface::NOT)));
        $testCase2->addAssert(new AssertEmpty($location, new Modifiers()));

        $testCase3 = new \MichaelHall\Webunit\TestCase($location, TestCaseInterface::METHOD_GET, Url::parse('http://localhost/baz'));
        $testCase3->addAssert(new AssertContains($location, 'Baz', new Modifiers()));

        $testSuite = new TestSuite();
        $testSuite->addTestCase($testCase1);
        $testSuite->addTestCase($testCase2);
        $testSuite->addTestCase($testCase3);

        $result = $testSuite->run($this->httpClient);

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
     * Test run with callback.
     */
    public function testRunWithCallback()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $assert1 = new AssertContains($location, 'Foo', new Modifiers(ModifiersInterface::NOT));
        $testCase1 = new \MichaelHall\Webunit\TestCase($location, TestCaseInterface::METHOD_GET, Url::parse('http://localhost/foo'));
        $testCase1->addAssert($assert1);

        $assert2 = new AssertEmpty($location, new Modifiers(ModifiersInterface::NOT));
        $testCase2 = new \MichaelHall\Webunit\TestCase($location, TestCaseInterface::METHOD_GET, Url::parse('http://localhost/bar'));
        $testCase2->addAssert($assert2);

        $testSuite = new TestSuite();
        $testSuite->addTestCase($testCase1);
        $testSuite->addTestCase($testCase2);

        /** @var AssertResultInterface[] $callbackResult */
        $callbackResult = [];
        $result = $testSuite->run($this->httpClient, function (AssertResultInterface $assertResult) use (&$callbackResult) {
            $callbackResult[] = $assertResult;
        });

        self::assertFalse($result->isSuccess());
        self::assertSame($testSuite, $result->getTestSuite());

        self::assertSame(2, count($result->getTestCaseResults()));
        self::assertSame($testCase1, $result->getTestCaseResults()[0]->getTestCase());
        self::assertFalse($result->getTestCaseResults()[0]->isSuccess());
        self::assertFalse($result->getTestCaseResults()[0]->getFailedAssertResult()->isSuccess());
        self::assertSame('Content "This is Foo page." contains "Foo"', $result->getTestCaseResults()[0]->getFailedAssertResult()->getError());
        self::assertSame($testCase2, $result->getTestCaseResults()[1]->getTestCase());
        self::assertTrue($result->getTestCaseResults()[1]->isSuccess());
        self::assertNull($result->getTestCaseResults()[1]->getFailedAssertResult());

        self::assertSame(1, count($result->getFailedTestCaseResults()));
        self::assertSame($testCase1, $result->getFailedTestCaseResults()[0]->getTestCase());
        self::assertFalse($result->getFailedTestCaseResults()[0]->isSuccess());
        self::assertFalse($result->getFailedTestCaseResults()[0]->getFailedAssertResult()->isSuccess());
        self::assertSame('Content "This is Foo page." contains "Foo"', $result->getFailedTestCaseResults()[0]->getFailedAssertResult()->getError());

        self::assertSame(4, count($callbackResult));
        self::assertTrue($callbackResult[0]->isSuccess());
        self::assertSame('', $callbackResult[0]->getError());
        self::assertInstanceOf(DefaultAssert::class, $callbackResult[0]->getAssert());
        self::assertFalse($callbackResult[1]->isSuccess());
        self::assertSame('Content "This is Foo page." contains "Foo"', $callbackResult[1]->getError());
        self::assertSame($assert1, $callbackResult[1]->getAssert());
        self::assertTrue($callbackResult[2]->isSuccess());
        self::assertSame('', $callbackResult[2]->getError());
        self::assertInstanceOf(DefaultAssert::class, $callbackResult[2]->getAssert());
        self::assertTrue($callbackResult[3]->isSuccess());
        self::assertSame('', $callbackResult[3]->getError());
        self::assertSame($assert2, $callbackResult[3]->getAssert());
    }

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new HttpClient(new TestRequestHandler());
    }

    /**
     * @var HttpClientInterface The HTTP client.
     */
    private HttpClientInterface $httpClient;
}
