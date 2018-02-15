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
use MichaelHall\Webunit\Modifiers;

/**
 * Class representing an assertion for equals test content.
 *
 * @since 1.0.0
 */
class AssertEquals implements AssertInterface
{
    /**
     * AssertEquals constructor.
     *
     * @since 1.0.0
     *
     * @param string    $content   The content to check for.
     * @param Modifiers $modifiers The modifiers.
     */
    public function __construct(string $content, Modifiers $modifiers)
    {
        $this->content = $content;
        $this->modifiers = $modifiers;
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
        $result = $pageResult->getContent() === $this->content;
        if ($this->modifiers->isNot()) {
            $result = !$result;
        }

        if ($result) {
            return new AssertResult($this);
        }

        return new AssertResult($this, false, 'Content "' . $pageResult->getContent() . '" ' . ($this->modifiers->isNot() ? 'equals' : 'does not equal') . ' "' . $this->content . '"');
    }

    /**
     * @var string My content to check for.
     */
    private $content;

    /**
     * @var Modifiers My modifiers.
     */
    private $modifiers;
}
