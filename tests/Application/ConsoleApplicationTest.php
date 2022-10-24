<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Application;

use DataTypes\System\FilePath;
use MichaelHall\HttpClient\HttpClient;
use MichaelHall\HttpClient\HttpClientInterface;
use MichaelHall\Webunit\Application\ConsoleApplication;
use MichaelHall\Webunit\Tests\Helpers\RequestHandlers\TestRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * Test ConsoleApplication class.
 */
class ConsoleApplicationTest extends TestCase
{
    /**
     * Test various command line parameter errors.
     *
     * @dataProvider commandLineParameterErrorsDataProvider
     *
     * @param array  $commandLineParameters The command line parameters.
     * @param string $expectedErrorMessage  The expected error message.
     */
    public function testCommandLineParameterErrors(array $commandLineParameters, string $expectedErrorMessage)
    {
        $consoleApplication = new ConsoleApplication($commandLineParameters, $this->httpClient);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(2, $result);
        self::assertSame(
            'Webunit v' . ConsoleApplication::WEBUNIT_VERSION . PHP_EOL .
            "\033[41m\033[1;37m" . $expectedErrorMessage . "\033[0m" . PHP_EOL .
            'Usage: webunit [options] testfile' . PHP_EOL,
            $output
        );
    }

    /**
     * Data provider for command line parameter errors test.
     *
     * @return array[]
     */
    public function commandLineParameterErrorsDataProvider(): array
    {
        return [
            [['webunit'], 'Missing testfile parameter.'],
            [['webunit', '--set=foo=bar'], 'Missing testfile parameter.'],
            [['webunit', "Foo\0Bar"], "Invalid path to testfile \"Foo\0Bar\": File path \"Foo\0Bar\" is invalid: Filename \"Foo\0Bar\" contains invalid character \"\0\"."],
            [['webunit', 'testfile', 'testfile2'], 'Extra testfile parameter "testfile2".'],
            [['webunit', 'testfile', '--'], 'Invalid option "--".'],
            [['webunit', '--foo', 'testfile'], 'Invalid option "--foo".'],
            [['webunit', '--Set', 'testfile'], 'Invalid option "--Set".'],
            [['webunit', '--set', 'testfile'], 'Missing value for option "--set".'],
            [['webunit', '--set=', 'testfile'], 'Invalid value for option "--set=": Missing variable name.'],
            [['webunit', '--set=F*o=Bar', 'testfile'], 'Invalid value for option "--set=F*o=Bar": Invalid variable name "F*o".'],
            [['webunit', '--set=Foo', 'testfile'], 'Invalid value for option "--set=Foo": Missing variable value.'],
        ];
    }

    /**
     * Test running application with missing test file.
     */
    public function testMissingTestFile()
    {
        $testfilePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/missing.webunit');

        $consoleApplication = new ConsoleApplication(['webunit', $testfilePath->__toString()], $this->httpClient);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(3, $result);
        self::assertSame(
            'Webunit v' . ConsoleApplication::WEBUNIT_VERSION . PHP_EOL .
            "\033[41m\033[1;37mCould not open file \"$testfilePath\".\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with parse errors in test file.
     */
    public function testParseErrors()
    {
        $testfilePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/parse-error.webunit');

        $consoleApplication = new ConsoleApplication(['webunit', $testfilePath->__toString()], $this->httpClient);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(4, $result);
        self::assertSame(
            'Webunit v' . ConsoleApplication::WEBUNIT_VERSION . PHP_EOL .
            "$testfilePath:4: Invalid argument: Invalid Url argument \"FooBar\" for \"get\": Url \"FooBar\" is invalid: Scheme is missing." . PHP_EOL .
            "$testfilePath:5: Syntax error: Invalid command \"baz\"." . PHP_EOL .
            "$testfilePath:7: Invalid argument: Status code -1 must be in range 100-599 for assert \"assert-status-code\"." . PHP_EOL .
            "$testfilePath:9: Missing variable: Missing variable value for \"set\" in \"FOO\"." . PHP_EOL .
            "$testfilePath:10: Invalid variable: Invalid variable name \"{Bar}\" for \"set-default\" in \"{Bar} = Baz\"." . PHP_EOL .
            "$testfilePath:12: Extra argument: \"BAZ\". No arguments are allowed for assert \"assert-empty\"." . PHP_EOL .
            "$testfilePath:15: Duplicate modifier: Modifier \"!\" is duplicated for assert \"assert-empty\"." . PHP_EOL .
            "$testfilePath:17: Invalid request modifier: Request modifier \"with-post-parameter\" is not allowed for request method \"GET\"." . PHP_EOL .
            "\033[41m\033[1;37mParsing failed.\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with empty test suite.
     */
    public function testEmptyTestSuite()
    {
        $testfilePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/no-tests.webunit');

        $consoleApplication = new ConsoleApplication(['webunit', $testfilePath->__toString()], $this->httpClient);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(1, $result);
        self::assertSame(
            'Webunit v' . ConsoleApplication::WEBUNIT_VERSION . PHP_EOL .
            "\033[43m\033[30mNo tests found.\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with successful test suite.
     */
    public function testSuccessfulTestSuite()
    {
        $testfilePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/success.webunit');

        $consoleApplication = new ConsoleApplication(['webunit', $testfilePath->__toString()], $this->httpClient);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(0, $result);
        self::assertSame(
            'Webunit v' . ConsoleApplication::WEBUNIT_VERSION . PHP_EOL .
            '.................' . PHP_EOL .
            "\033[42m\033[30m6 tests completed successfully.\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with failed test suite.
     */
    public function testFailedTestSuite()
    {
        $testfilePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/fail.webunit');

        $consoleApplication = new ConsoleApplication(['webunit', $testfilePath->__toString()], $this->httpClient);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(5, $result);
        self::assertSame(
            'Webunit v' . ConsoleApplication::WEBUNIT_VERSION . PHP_EOL .
            "..\033[41m\033[1;37mF\033[0m\033[41m\033[1;37mF\033[0m.\033[41m\033[1;37mF\033[0m.\033[41m\033[1;37mF\033[0m" . PHP_EOL .
            "$testfilePath:4: Test failed: https://example.com/foo: Content \"This is Foo page.\" contains \"foo\" (case insensitive)." . PHP_EOL .
            "$testfilePath:6: Test failed: https://example.com/foobar: Status code 404 was returned." . PHP_EOL .
            "$testfilePath:10: Test failed: https://example.com/method: Content \"Method is POST\" equals \"Method is POST\"." . PHP_EOL .
            "$testfilePath:14: Test failed: https://example.com/request: Content \"Post Field \"Foo\" = \"Bar\"\" does not contain \"Post Field \"Foo\" = \"Baz\"\"." . PHP_EOL .
            "\033[41m\033[1;37m4 tests failed.\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with successful test suite with set command line parameters.
     */
    public function testSuccessfulTestSuiteWithSet()
    {
        $testfilePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/set-required.webunit');

        $consoleApplication = new ConsoleApplication(['webunit', '--set=DOMAIN=example.com', $testfilePath->__toString(), '--set=EXPECTED_CONTENT=Hello World!'], $this->httpClient);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(0, $result);
        self::assertSame(
            'Webunit v' . ConsoleApplication::WEBUNIT_VERSION . PHP_EOL .
            '...' . PHP_EOL .
            "\033[42m\033[30m1 test completed successfully.\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with failed test suite with set command line parameters.
     */
    public function testFailedTestSuiteWithSet()
    {
        $testfilePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/set-required.webunit');

        $consoleApplication = new ConsoleApplication(['webunit', '--set=DOMAIN=example.com', $testfilePath->__toString(), '--set=EXPECTED_CONTENT=Foo'], $this->httpClient);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(5, $result);
        self::assertSame(
            'Webunit v' . ConsoleApplication::WEBUNIT_VERSION . PHP_EOL .
            ".\033[41m\033[1;37mF\033[0m" . PHP_EOL .
            "$testfilePath:4: Test failed: https://example.com/: Content \"Hello World!\" does not equal \"Foo\"." . PHP_EOL .
            "\033[41m\033[1;37m1 test failed.\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with a large test suite.
     */
    public function testLargeTestSuite()
    {
        $testfilePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/many-tests.webunit');

        $consoleApplication = new ConsoleApplication(['webunit', $testfilePath->__toString()], $this->httpClient);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(0, $result);
        self::assertSame(
            'Webunit v' . ConsoleApplication::WEBUNIT_VERSION . PHP_EOL .
            '......................................................................' . PHP_EOL .
            '..........' . PHP_EOL .
            "\033[42m\033[30m80 tests completed successfully.\033[0m" . PHP_EOL,
            $output
        );
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
