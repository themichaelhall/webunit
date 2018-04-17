<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions;

use MichaelHall\Webunit\Assertions\Base\AbstractAssert;
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
     * @param int       $statusCode The status code.
     * @param Modifiers $modifiers  The modifiers.
     */
    public function __construct(int $statusCode, Modifiers $modifiers)
    {
        parent::__construct($modifiers);

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
     * @return Modifiers The allowed modifiers.
     */
    protected function getAllowedModifiers(): Modifiers
    {
        return new Modifiers(Modifiers::NOT);
    }

    /**
     * @var int My status code.
     */
    private $statusCode;
}