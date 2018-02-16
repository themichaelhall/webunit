<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions\Base;

use MichaelHall\Webunit\AssertResult;
use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\AssertResultInterface;
use MichaelHall\Webunit\Interfaces\PageResultInterface;
use MichaelHall\Webunit\Modifiers;

/**
 * Abstract assertion base class.
 *
 * @since 1.0.0
 */
abstract class AbstractAssert implements AssertInterface
{
    /**
     * Returns the content.
     *
     * @since 1.0.0
     *
     * @return string The content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns the modifiers.
     *
     * @since 1.0.0
     *
     * @return Modifiers The modifiers.
     */
    public function getModifiers(): Modifiers
    {
        return $this->modifiers;
    }

    /**
     * Test assertion against a page result.
     *
     * @since 1.0.0
     *
     * @param PageResultInterface $pageResult The page result.
     *
     * @return AssertResultInterface The test result.
     */
    public function test(PageResultInterface $pageResult): AssertResultInterface
    {
        $result = $this->onTest($pageResult);
        if ($this->getModifiers()->isNot()) {
            $result = !$result;
        }

        if (!$result) {
            return new AssertResult($this, false, $this->onFail($pageResult));
        }

        return new AssertResult($this);
    }

    /**
     * Creates an abstract assert.
     *
     * @since 1.0.0
     *
     * @param string    $content   The content.
     * @param Modifiers $modifiers The modifiers.
     */
    protected function __construct(string $content, Modifiers $modifiers)
    {
        $this->content = $content;
        $this->modifiers = $modifiers;
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
    abstract protected function onTest(PageResultInterface $pageResult): bool;

    /**
     * Called when a test failed.
     *
     * @since 1.0.0
     *
     * @param PageResultInterface $pageResult The page result.
     *
     * @return string The error text.
     */
    abstract protected function onFail(PageResultInterface $pageResult): string;

    /**
     * @var string My content to check for.
     */
    private $content;

    /**
     * @var Modifiers My modifiers.
     */
    private $modifiers;
}
