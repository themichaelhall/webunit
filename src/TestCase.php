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
use MichaelHall\Webunit\Exceptions\IncompatibleRequestModifierException;
use MichaelHall\Webunit\Exceptions\MethodNotAllowedForRequestModifierException;
use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\RequestModifierInterface;
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
     * @param string            $method   The method.
     * @param UrlInterface      $url      The url.
     */
    public function __construct(LocationInterface $location, string $method, UrlInterface $url)
    {
        $this->url = $url;
        $this->method = $method;
        $this->asserts = [new DefaultAssert($location)];
        $this->requestModifiers = [];
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
     * Adds a request modifier.
     *
     * @since 2.1.0
     *
     * @param RequestModifierInterface $requestModifier The request modifier.
     */
    public function addRequestModifier(RequestModifierInterface $requestModifier): void
    {
        if (!$requestModifier->isAllowedForMethod($this->method)) {
            throw new MethodNotAllowedForRequestModifierException($this->method, $requestModifier);
        }

        foreach ($this->requestModifiers as $currentRequestModifier) {
            if (!$currentRequestModifier->isCompatibleWith($requestModifier)) {
                throw new IncompatibleRequestModifierException($currentRequestModifier, $requestModifier);
            }
        }

        $this->requestModifiers[] = $requestModifier;
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
     * Returns the method.
     *
     * @since 2.1.0
     *
     * @return string The method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns the request modifiers.
     *
     * @since 2.1.0
     *
     * @return RequestModifierInterface[] The request modifiers.
     */
    public function getRequestModifiers(): array
    {
        return $this->requestModifiers;
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
        $httpClientRequest = new HttpClientRequest($this->url, $this->method);
        foreach ($this->requestModifiers as $requestModifier) {
            $requestModifier->modifyRequest($httpClientRequest);
        }

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
     * @var string The method.
     */
    private string $method;

    /**
     * @var AssertInterface[] The asserts.
     */
    private array $asserts;

    /**
     * @var RequestModifierInterface[] The request modifiers.
     */
    private array $requestModifiers;
}
