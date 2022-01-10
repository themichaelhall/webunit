<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions;

use MichaelHall\Webunit\Assertions\Base\AbstractAssert;
use MichaelHall\Webunit\Exceptions\InvalidParameterException;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Interfaces\PageResultInterface;
use MichaelHall\Webunit\Modifiers;

/**
 * Class representing an assertion for a status code.
 *
 * @since 1.0.0
 */
class AssertStatusCode extends AbstractAssert
{
    /**
     * AssertStatusCode constructor.
     *
     * @since 1.0.0
     *
     * @param LocationInterface  $location   The location.
     * @param int                $statusCode The status code.
     * @param ModifiersInterface $modifiers  The modifiers.
     */
    public function __construct(LocationInterface $location, int $statusCode, ModifiersInterface $modifiers)
    {
        parent::__construct($location, $modifiers);

        if ($statusCode < 100 || $statusCode > 599) {
            throw new InvalidParameterException('Status code ' . $statusCode . ' must be in range 100-599');
        }

        $this->statusCode = $statusCode;
    }

    /**
     * Called when a test is performed on a page result.
     *
     * @since 1.0.0
     *
     * @param PageResultInterface $pageResult The page result.
     *
     * @return bool True if test was successful, false otherwise.
     */
    protected function onTest(PageResultInterface $pageResult): bool
    {
        return $pageResult->getStatusCode() === $this->statusCode;
    }

    /**
     * Called when a test failed.
     *
     * @since 1.0.0
     *
     * @param PageResultInterface $pageResult The page result.
     *
     * @return string The error text.
     */
    protected function onFail(PageResultInterface $pageResult): string
    {
        return 'Status code ' . $pageResult->getStatusCode() . ' ' . ($this->getModifiers()->isNot() ? 'equals' : 'does not equal') . ' ' . $this->statusCode;
    }

    /**
     * Returns the allowed modifiers for assert.
     *
     * @since 1.0.0
     *
     * @return ModifiersInterface The allowed modifiers.
     */
    protected function getAllowedModifiers(): ModifiersInterface
    {
        return new Modifiers(ModifiersInterface::NOT);
    }

    /**
     * @var int My status code.
     */
    private $statusCode;
}
