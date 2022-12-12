<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

use DataTypes\Net\UrlInterface;
use MichaelHall\HttpClient\HttpClientInterface;

/**
 * Interface for a test case.
 *
 * @since 1.0.0
 */
interface TestCaseInterface
{
    /**
     * DELETE method.
     *
     * @since 2.1.0
     */
    public const METHOD_DELETE = 'DELETE';

    /**
     * GET method.
     *
     * @since 2.1.0
     */
    public const METHOD_GET = 'GET';

    /**
     * PATCH method.
     *
     * @since 2.1.0
     */
    public const METHOD_PATCH = 'PATCH';

    /**
     * POST method.
     *
     * @since 2.1.0
     */
    public const METHOD_POST = 'POST';

    /**
     * PUT method.
     *
     * @since 2.1.0
     */
    public const METHOD_PUT = 'PUT';

    /**
     * Adds an assert.
     *
     * @since 1.0.0
     *
     * @param AssertInterface $assert The assert.
     */
    public function addAssert(AssertInterface $assert): void;

    /**
     * Adds a request modifier.
     *
     * @since 2.1.0
     *
     * @param RequestModifierInterface $requestModifier The request modifier.
     */
    public function addRequestModifier(RequestModifierInterface $requestModifier): void;

    /**
     * Returns the asserts.
     *
     * @since 1.0.0
     *
     * @return AssertInterface[] The asserts.
     */
    public function getAsserts(): array;

    /**
     * Returns the method.
     *
     * @since 2.1.0
     *
     * @return string The method.
     */
    public function getMethod(): string;

    /**
     * Returns the request modifiers.
     *
     * @since 2.1.0
     *
     * @return RequestModifierInterface[] The request modifiers.
     */
    public function getRequestModifiers(): array;

    /**
     * Returns the url.
     *
     * @since 1.0.0
     *
     * @return UrlInterface The url.
     */
    public function getUrl(): UrlInterface;

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
