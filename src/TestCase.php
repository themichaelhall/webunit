<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit;

use DataTypes\Url;
use MichaelHall\PageFetcher\Interfaces\PageFetcherInterface;
use MichaelHall\PageFetcher\PageFetcherRequest;
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
     */
    public function __construct()
    {
        $this->asserts = [];
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
        $pageFetcherRequest = new PageFetcherRequest(Url::parse('http://localhost')); // fixme: set url in constructor.
        $pageFetcherResult = $pageFetcher->fetch($pageFetcherRequest);
        $pageResult = new PageResult($pageFetcherResult->getContent());

        foreach ($this->getAsserts() as $assert) {
            $assertResult = $assert->test($pageResult);

            if (!$assertResult->isSuccess()) {
                return new TestCaseResult($this, false, $assertResult);
            }
        }

        return new TestCaseResult($this);
    }

    /**
     * @var AssertInterface[] My asserts.
     */
    private $asserts;
}
