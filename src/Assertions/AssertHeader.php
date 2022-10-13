<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions;

use MichaelHall\Webunit\Assertions\Base\AbstractAssert;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Interfaces\PageResultInterface;
use MichaelHall\Webunit\Modifiers;

/**
 * Class representing an assertion for a header.
 *
 * @since 1.1.0
 */
class AssertHeader extends AbstractAssert
{
    /**
     * AssertHeader constructor.
     *
     * @since 1.1.0
     *
     * @param LocationInterface  $location
     * @param string             $header
     * @param ModifiersInterface $modifiers
     */
    public function __construct(LocationInterface $location, string $header, ModifiersInterface $modifiers)
    {
        parent::__construct($location, $modifiers);

        [$this->headerName, $this->headerValue] = self::splitHeader($header);
    }

    /**
     * Returns the header name.
     *
     * @since 1.3.0
     *
     * @return string The header name.
     */
    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    /**
     * Returns the header value or null if no header value is set.
     *
     * @since 1.3.0
     *
     * @return string|null The header value or null if no header value is set.
     */
    public function getHeaderValue(): ?string
    {
        return $this->headerValue;
    }

    /**
     * Called when a test is performed on a page result.
     *
     * @since 1.1.0
     *
     * @param PageResultInterface $pageResult The page result.
     *
     * @return bool True if test was successful, false otherwise.
     */
    protected function onTest(PageResultInterface $pageResult): bool
    {
        $headers = $pageResult->getHeaders();
        foreach ($headers as $header) {
            [$headerName, $headerValue] = self::splitHeader($header);

            if ($this->nameAndValueMatches($headerName, $headerValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Called when a test failed.
     *
     * @since 1.1.0
     *
     * @param PageResultInterface $pageResult The page result.
     *
     * @return string The error text.
     */
    protected function onFail(PageResultInterface $pageResult): string
    {
        $headerString = implode(
            ', ',
            array_map(function (string $s) {
                return '"' . $s . '"';
            }, $pageResult->getHeaders())
        );

        if ($headerString === '') {
            $headerString = '""';
        }

        $result = 'Headers ' . $headerString . ' ' . ($this->getModifiers()->isNot() ? 'contains' : 'does not contain') . ' a header with name "' . $this->headerName . '"';
        if ($this->headerValue !== null) {
            $result .= ' and value "' . $this->headerValue . '"';
        }

        return $result;
    }

    /**
     * Returns the allowed modifiers for assert.
     *
     * @since 1.1.0
     *
     * @return ModifiersInterface The allowed modifiers.
     */
    protected function getAllowedModifiers(): ModifiersInterface
    {
        return new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP);
    }

    /**
     * Check if a header name and an optional value matches this assert.
     *
     * @param string      $headerName  The header name.
     * @param string|null $headerValue The header value.
     *
     * @return bool True if header name and value match, false otherwise.
     */
    private function nameAndValueMatches(string $headerName, ?string $headerValue): bool
    {
        if (!$this->stringEqualsCaseInsensitive($this->headerName, $headerName)) {
            return false;
        }

        if ($this->headerValue === null) {
            return true;
        }

        if ($headerValue === null) {
            return false;
        }

        if (!$this->stringEquals($this->headerValue, $headerValue)) {
            return false;
        }

        return true;
    }

    /**
     * Splits a header into a name and an optional value.
     *
     * @param string $header The header to split.
     *
     * @return array{string, string|null} The result, containing either [name, null] or [name, value].
     */
    private static function splitHeader(string $header): array
    {
        $headerParts = explode(':', $header, 2);

        $headerName = $headerParts[0];
        $headerValue = count($headerParts) > 1 ? ltrim($headerParts[1]) : null;

        return [$headerName, $headerValue];
    }

    /**
     * @var string The header name.
     */
    private string $headerName;

    /**
     * @var string|null The header value.
     */
    private ?string $headerValue;
}
