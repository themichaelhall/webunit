<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit;

use DataTypes\Interfaces\UrlInterface;
use MichaelHall\PageFetcher\Interfaces\PageFetcherInterface;
use MichaelHall\PageFetcher\PageFetcherRequest;
use MichaelHall\Webunit\Assertions\AssertStatusCode;
use MichaelHall\Webunit\Assertions\DefaultAssert;
use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
use MichaelHall\Webunit\Interfaces\TestCaseResultInterface;

/**
 * Class representing a test case.
 *
 * @since 1.0.0
 */
class TestCase implements TestCaseInterface
{
    /**
     * Constructs a test case.
     *
     * @since 1.0.0
     *
     * @param UrlInterface $url The url.
     */
    public function __construct(UrlInterface $url)
    {
        $this->url = $url;
        $this->asserts = [new DefaultAssert()];
    }

    /**
     * Adds an assert.
     *
     * @since 1.0.0
     *
     * @param AssertInterface $assert The assert.
     */
    public function addAssert(AssertInterface $assert): void
    {
        $this->asserts[] = $assert;

        if ($assert instanceof AssertStatusCode) {
            $this->removeDefaultAssert();
        }
    }

    /**
     * Returns the asserts.
     *
     * @since 1.0.0
     *
     * @return AssertInterface[] The asserts.
     */
    public function getAsserts(): array
    {
        return $this->asserts;
    }

    /**
     * Returns the url.
     *
     * @since 1.0.0
     *
     * @return UrlInterface The url.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Runs the test case.
     *
     * @since 1.0.0
     *
     * @param PageFetcherInterface $pageFetcher The page fetcher.
     *
     * @return TestCaseResultInterface The result.
     */
    public function run(PageFetcherInterface $pageFetcher): TestCaseResultInterface
    {
        $pageFetcherRequest = new PageFetcherRequest($this->url);
        $pageFetcherResult = $pageFetcher->fetch($pageFetcherRequest);

        $pageResult = new PageResult(
            $pageFetcherResult->getHttpCode(),
            $pageFetcherResult->getContent()
        );

        foreach ($this->getAsserts() as $assert) {
            $assertResult = $assert->test($pageResult);

            if (!$assertResult->isSuccess()) {
                return new TestCaseResult($this, $assertResult);
            }
        }

        return new TestCaseResult($this);
    }

    /**
     * Removes default assert from my asserts.
     */
    private function removeDefaultAssert(): void
    {
        $this->asserts = array_values(array_filter($this->asserts, function (AssertInterface $assert) {
            return !($assert instanceof DefaultAssert);
        }));
    }

    /**
     * @var UrlInterface My url.
     */
    private $url;

    /**
     * @var AssertInterface[] My asserts.
     */
    private $asserts;
}
