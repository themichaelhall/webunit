<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Application;

use DataTypes\FilePath;
use MichaelHall\PageFetcher\FakePageFetcher;
use MichaelHall\PageFetcher\Interfaces\PageFetcherInterface;
use MichaelHall\PageFetcher\Interfaces\PageFetcherRequestInterface;
use MichaelHall\PageFetcher\Interfaces\PageFetcherResponseInterface;
use MichaelHall\PageFetcher\PageFetcherResponse;
use MichaelHall\Webunit\Application\ConsoleApplication;
use PHPUnit\Framework\TestCase;

/**
 * Test ConsoleApplication class.
 */
class ConsoleApplicationTest extends TestCase
{
    /**
     * Test running application with missing file argument.
     */
    public function testMissingTestFileArgument()
    {
        $consoleApplication = new ConsoleApplication(1, ['webunit'], $this->pageFetcher);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(1, $result);
        self::assertSame(
            'Webunit [dev] by Michael Hall.' . PHP_EOL .
            "\033[41m\033[1;37mUsage: webunit testfile\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with invalid file argument.
     */
    public function testInvalidTestFileArgument()
    {
        $consoleApplication = new ConsoleApplication(2, ['webunit', "Foo\0Bar"], $this->pageFetcher);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(2, $result);
        self::assertSame(
            'Webunit [dev] by Michael Hall.' . PHP_EOL .
            "\033[41m\033[1;37mInvalid file path \"Foo\0Bar\": File path \"Foo\0Bar\" is invalid: Filename \"Foo\0Bar\" contains invalid character \"\0\".\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with missing test file.
     */
    public function testMissingTestFile()
    {
        $consoleApplication = new ConsoleApplication(2, ['webunit', __DIR__ . '/../Helpers/WebunitTests/missing.webunit'], $this->pageFetcher);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        $filePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/missing.webunit');

        self::assertSame(2, $result);
        self::assertSame(
            'Webunit [dev] by Michael Hall.' . PHP_EOL .
            "\033[41m\033[1;37mCould not open file \"{$filePath}\".\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with parse errors in test file.
     */
    public function testParseErrors()
    {
        $consoleApplication = new ConsoleApplication(2, ['webunit', __DIR__ . '/../Helpers/WebunitTests/parse-error.webunit'], $this->pageFetcher);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        $filePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/parse-error.webunit');

        self::assertSame(3, $result);
        self::assertSame(
            'Webunit [dev] by Michael Hall.' . PHP_EOL .
            "{$filePath}:4: Invalid argument: Invalid Url argument \"FooBar\" for \"GET\": Url \"FooBar\" is invalid: Scheme is missing." . PHP_EOL .
            "{$filePath}:5: Syntax error: Invalid command \"Baz\"." . PHP_EOL .
            "\033[41m\033[1;37mParsing failed\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with successful test suite.
     */
    public function testSuccessfulTestSuite()
    {
        $consoleApplication = new ConsoleApplication(2, ['webunit', __DIR__ . '/../Helpers/WebunitTests/success.webunit'], $this->pageFetcher);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        self::assertSame(0, $result);
        self::assertSame(
            'Webunit [dev] by Michael Hall.' . PHP_EOL .
            "\033[42m\033[30mTests completed successfully\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Test running application with failed test suite.
     */
    public function testFailedTestSuite()
    {
        $consoleApplication = new ConsoleApplication(2, ['webunit', __DIR__ . '/../Helpers/WebunitTests/fail.webunit'], $this->pageFetcher);

        ob_start();
        $result = $consoleApplication->run();
        $output = ob_get_contents();
        ob_end_clean();

        $filePath = FilePath::parse(__DIR__ . '/../Helpers/WebunitTests/fail.webunit');

        self::assertSame(4, $result);
        self::assertSame(
            'Webunit [dev] by Michael Hall.' . PHP_EOL .
            "{$filePath}:3: Test failed: https://example.com/baz: Status code 404 was returned." . PHP_EOL .
            "\033[41m\033[1;37mTests failed\033[0m" . PHP_EOL,
            $output
        );
    }

    /**
     * Set up.
     */
    public function setUp()
    {
        $this->pageFetcher = new FakePageFetcher();

        $this->pageFetcher->setResponseHandler(function (PageFetcherRequestInterface $request): PageFetcherResponseInterface {
            switch ($request->getUrl()->getPath()) {
                case '/':
                    return new PageFetcherResponse(200, 'Hello World!');
                case '/foo':
                    return new PageFetcherResponse(200, 'This is Foo page.');
                case '/bar':
                    return new PageFetcherResponse(200, 'This is Bar page.');
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
     * @var PageFetcherInterface My page fetcher.
     */
    private $pageFetcher;
}
