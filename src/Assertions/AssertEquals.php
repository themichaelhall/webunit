<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions;

use MichaelHall\Webunit\Assertions\Base\AbstractContentAssert;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\PageResultInterface;
use MichaelHall\Webunit\Modifiers;

/**
 * Class representing an assertion for equals test content.
 *
 * @since 1.0.0
 */
class AssertEquals extends AbstractContentAssert
{
    /**
     * AssertEquals constructor.
     *
     * @since 1.0.0
     *
     * @param LocationInterface $location  The location.
     * @param string            $content   The content to check for.
     * @param Modifiers         $modifiers The modifiers.
     */
    public function __construct(LocationInterface $location, string $content, Modifiers $modifiers)
    {
        parent::__construct($location, $content, $modifiers);
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
        return $this->stringEquals($this->getContent(), $pageResult->getContent());
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
        return 'Content "' . $pageResult->getContent() . '" ' . ($this->getModifiers()->isNot() ? 'equals' : 'does not equal') . ' "' . $this->getContent() . '"';
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
        return new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP);
    }
}
