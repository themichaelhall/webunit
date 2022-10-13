<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit;

use DataTypes\Net\UrlInterface;
use MichaelHall\HttpClient\HttpClientInterface;
use MichaelHall\HttpClient\HttpClientRequest;
use MichaelHall\Webunit\Assertions\AssertStatusCode;
use MichaelHall\Webunit\Assertions\DefaultAssert;
use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\LocationInterface;
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
     * @param LocationInterface $location The location.
     * @param UrlInterface      $url      The url.
     */
    public function __construct(LocationInterface $location, UrlInterface $url)
    {
        $this->url = $url;
        $this->asserts = [new DefaultAssert($location)];
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
    public function getUrl(): UrlInterface
    {
        return $this->url;
    }

    /**
     * Runs the test case.
     *
     * @since 1.0.0
     *
     * @param HttpClientInterface $httpClient The HTTP client.
     * @param callable|null       $callback   An optional callback method to call after each assert. The method takes a AssertResultInterface as a parameter.
     *
     * @return TestCaseResultInterface The result.
     */
    public function run(HttpClientInterface $httpClient, ?callable $callback = null): TestCaseResultInterface
    {
        $httpClientRequest = new HttpClientRequest($this->url);
        $httpClientResponse = $httpClient->send($httpClientRequest);

        $pageResult = new PageResult(
            $httpClientResponse->getHttpCode(),
            $httpClientResponse->getHeaders(),
            $httpClientResponse->getContent()
        );

        foreach ($this->getAsserts() as $assert) {
            $assertResult = $assert->test($pageResult);

            if ($callback !== null) {
                $callback($assertResult);
            }

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
        $this->asserts = array_values(
            array_filter($this->asserts, function (AssertInterface $assert) {
                return !($assert instanceof DefaultAssert);
            })
        );
    }

    /**
     * @var UrlInterface The url.
     */
    private UrlInterface $url;

    /**
     * @var AssertInterface[] The asserts.
     */
    private array $asserts;
}
