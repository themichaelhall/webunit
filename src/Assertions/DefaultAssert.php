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
 * Class representing a default assertion.
 *
 * @since 1.0.0
 */
class DefaultAssert extends AbstractAssert
{
    /**
     * DefaultAssert constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct(new Modifiers());
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
        return $pageResult->getStatusCode() >= 200 && $pageResult->getStatusCode() < 300;
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
        return 'Status code ' . $pageResult->getStatusCode() . ' was returned';
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
        return new Modifiers();
    }
}
