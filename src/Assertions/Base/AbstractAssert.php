<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions\Base;

use MichaelHall\Webunit\AssertResult;
use MichaelHall\Webunit\Exceptions\NotAllowedModifierException;
use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\AssertResultInterface;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
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
     * Creates an abstract assert.
     *
     * @since 1.0.0
     *
     * @param LocationInterface  $location  The location.
     * @param ModifiersInterface $modifiers The modifiers.
     *
     * @throws NotAllowedModifierException If modifiers are not allowed for this assert.
     */
    public function __construct(LocationInterface $location, ModifiersInterface $modifiers)
    {
        $this->setModifiers($modifiers);
        $this->location = $location;
    }

    /**
     * Returns the location.
     *
     * @since 1.0.0
     *
     * @return LocationInterface The location.
     */
    public function getLocation(): LocationInterface
    {
        return $this->location;
    }

    /**
     * Returns the modifiers.
     *
     * @since 1.0.0
     *
     * @return ModifiersInterface The modifiers.
     */
    public function getModifiers(): ModifiersInterface
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
     * Sets the modifiers.
     *
     * @since 1.0.0
     *
     * @param ModifiersInterface $modifiers The modifiers.
     *
     * @throws NotAllowedModifierException If modifiers are not allowed for this assert.
     *
     * @return AssertInterface Self.
     */
    protected function setModifiers(ModifiersInterface $modifiers): AssertInterface
    {
        $allowedModifiers = $this->getAllowedModifiers();
        $notAllowedModifiesValues = ModifiersInterface::NONE;

        if ($modifiers->isCaseInsensitive() && !$allowedModifiers->isCaseInsensitive()) {
            $notAllowedModifiesValues |= ModifiersInterface::CASE_INSENSITIVE;
        }

        if ($modifiers->isRegexp() && !$allowedModifiers->isRegexp()) {
            $notAllowedModifiesValues |= ModifiersInterface::REGEXP;
        }

        if ($notAllowedModifiesValues !== ModifiersInterface::NONE) {
            throw new NotAllowedModifierException(new Modifiers($notAllowedModifiesValues));
        }

        $this->modifiers = $modifiers;

        return $this;
    }

    /**
     * Returns true if value contains the expected value, taking the current modifiers into account.
     *
     * @since 1.0.0
     *
     * @param string $expected The expected value.
     * @param string $value    The value.
     *
     * @return bool True if value contains the expected value, false otherwise.
     */
    protected function stringContains(string $expected, string $value): bool
    {
        if ($this->getModifiers()->isRegexp()) {
            return preg_match('/' . $expected . '/' . ($this->getModifiers()->isCaseInsensitive() ? 'i' : ''), $value) === 1;
        }

        return $this->getModifiers()->isCaseInsensitive() ?
            mb_stristr($value, $expected) !== false :
            str_contains($value, $expected);
    }

    /**
     * Returns true if strings are equal, taking the current modifiers into account.
     *
     * @since 1.0.0
     *
     * @param string $expected The expected value.
     * @param string $value    The value.
     *
     * @return bool True if strings are equal, false otherwise.
     */
    protected function stringEquals(string $expected, string $value): bool
    {
        return self::checkStringEquals($expected, $value, $this->modifiers->isCaseInsensitive(), $this->modifiers->isRegexp());
    }

    /**
     * Returns true if strings are equal, always case-insensitive, otherwise taking the current modifiers into account.
     *
     * @since 1.1.0
     *
     * @param string $expected The expected value.
     * @param string $value    The value.
     *
     * @return bool True if strings are equal, false otherwise.
     */
    protected function stringEqualsCaseInsensitive(string $expected, string $value): bool
    {
        return self::checkStringEquals($expected, $value, true, $this->modifiers->isRegexp());
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
     * Returns the allowed modifiers for assert.
     *
     * @since 1.0.0
     *
     * @return ModifiersInterface The allowed modifiers.
     */
    abstract protected function getAllowedModifiers(): ModifiersInterface;

    /**
     * Checks if strings should be considered equal.
     *
     * @param string $expected          The expected value.
     * @param string $value             The value.
     * @param bool   $isCaseInsensitive If true, check case-insensitive.
     * @param bool   $isRegexp          If true, the expected value is a regular expression.
     *
     * @return bool True if strings are considered equal, false otherwise.
     */
    private static function checkStringEquals(string $expected, string $value, bool $isCaseInsensitive, bool $isRegexp): bool
    {
        if ($isRegexp) {
            return preg_match('/^' . $expected . '$/' . ($isCaseInsensitive ? 'i' : ''), $value) === 1;
        }

        return $isCaseInsensitive ?
            mb_strtolower($expected) === mb_strtolower($value) :
            $expected === $value;
    }

    /**
     * @var ModifiersInterface My modifiers.
     */
    private $modifiers;

    /**
     * @var LocationInterface My location.
     */
    private $location;
}
