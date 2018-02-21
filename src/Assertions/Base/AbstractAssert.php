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
     * Sets the modifiers.
     *
     * @since 1.0.0
     *
     * @param Modifiers $modifiers The modifiers.
     *
     * @return AssertInterface Self.
     */
    public function setModifiers(Modifiers $modifiers): AssertInterface
    {
        $this->modifiers = $modifiers;

        return $this;
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
            $error = $this->onFail($pageResult);
            $modifiers = $this->getModifiers()->__toString();
            if ($modifiers !== '') {
                $error .= ' ' . $modifiers;
            }

            return new AssertResult($this, false, $error);
        }

        return new AssertResult($this);
    }

    /**
     * Creates an abstract assert.
     *
     * @since 1.0.0
     */
    protected function __construct()
    {
        $this->modifiers = new Modifiers();
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
     * @var Modifiers My modifiers.
     */
    private $modifiers;
}
