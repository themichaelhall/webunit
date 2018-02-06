<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions;

use MichaelHall\Webunit\Interfaces\AssertInterface;
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
     * @param string              $error      The error text if assertion failed.
     *
     * @return bool True if assertion was successful, false otherwise.
     */
    public function test(PageResultInterface $pageResult, string &$error = null): bool
    {
        $result = strpos($pageResult->getContent(), $this->content) !== false;
        if (!$result) {
            $error = 'Content "' . $pageResult->getContent() . '" does not contain "' . $this->content . '"';
        }

        return $result;
    }

    /**
     * @var string My content to check for.
     */
    private $content;
}
