<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

/**
 * Interface for assertions.
 *
 * @since 1.0.0
 */
interface AssertInterface
{
    /**
     * Test assertion against a page result.
     *
     * @since 1.0.0
     *
     * @param PageResultInterface $pageResult The page result.
     *
     * @return bool True if assertion was successful, false otherwise.
     */
    public function test(PageResultInterface $pageResult): bool;
}
