<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit;

use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;

/**
 * Class representing a test case.
 *
 * @since 1.0.0
 */
class TestCase implements TestCaseInterface
{
    /**
     * Constructs a test case.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->asserts = [];
    }

    /**
     * Adds an assert.
     *
     * @since 1.0.0
     *
     * @param AssertInterface $assert The assert.
     */
    public function addAssert(AssertInterface $assert): void
    {
        $this->asserts[] = $assert;
    }

    /**
     * Returns the asserts.
     *
     * @since 1.0.0
     *
     * @return AssertInterface[] The asserts.
     */
    public function getAsserts(): array
    {
        return $this->asserts;
    }

    /**
     * @var AssertInterface[] My asserts.
     */
    private $asserts;
}
