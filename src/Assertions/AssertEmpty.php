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
 * Class representing an assertion for empty content.
 *
 * @since 1.0.0
 */
class AssertEmpty extends AbstractAssert
{
    /**
     * AssertEmpty constructor.
     *
     * @since 1.0.0
     *
     * @param Modifiers $modifiers The modifiers.
     */
    public function __construct(Modifiers $modifiers)
    {
        parent::__construct($modifiers);
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
        return $pageResult->getContent() === '';
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
        return 'Content "' . $pageResult->getContent() . '" ' . ($this->getModifiers()->isNot() ? 'is empty' : 'is not empty');
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
}
