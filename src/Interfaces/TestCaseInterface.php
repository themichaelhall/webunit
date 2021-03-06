<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

use DataTypes\Interfaces\UrlInterface;
use MichaelHall\HttpClient\HttpClientInterface;

/**
 * Interface for a test case.
 *
 * @since 1.0.0
 */
interface TestCaseInterface
{
    /**
     * Adds an assert.
     *
     * @since 1.0.0
     *
     * @param AssertInterface $assert The assert.
     */
    public function addAssert(AssertInterface $assert): void;

    /**
     * Returns the asserts.
     *
     * @since 1.0.0
     *
     * @return AssertInterface[] The asserts.
     */
    public function getAsserts(): array;

    /**
     * Returns the url.
     *
     * @since 1.0.0
     *
     * @return UrlInterface The url.
     */
    public function getUrl();

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
    public function run(HttpClientInterface $httpClient, ?callable $callback = null): TestCaseResultInterface;
}
