<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions;

use MichaelHall\Webunit\AssertResult;
use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\AssertResultInterface;
use MichaelHall\Webunit\Interfaces\PageResultInterface;

/**
 * Class representing an assertion for containing test content.
 *
 * @since 1.0.0
 */
class AssertContains implements AssertInterface
{
    /**
     * AssertContains constructor.
     *
     * @since 1.0.0
     *
     * @param string $content The content to check for.
     */
    public function __construct(string $content)
    {
        $this->content = $content;
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
        if (strpos($pageResult->getContent(), $this->content) !== false) {
            return new AssertResult($this);
        }

        return new AssertResult($this, false, 'Content "' . $pageResult->getContent() . '" does not contain "' . $this->content . '"');
    }

    /**
     * @var string My content to check for.
     */
    private $content;
}
