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
 * Class representing an assertion for equals test content.
 *
 * @since 1.0.0
 */
class AssertEquals extends AbstractAssert
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
        parent::__construct($modifiers);

        $this->content = $content;
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
        return $this->getModifiers()->isCaseInsensitive() ?
            mb_strtolower($pageResult->getContent()) === mb_strtolower($this->content) :
            $pageResult->getContent() === $this->content;
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
        return 'Content "' . $pageResult->getContent() . '" ' . ($this->getModifiers()->isNot() ? 'equals' : 'does not equal') . ' "' . $this->content . '"';
    }

    /**
     * @var string My content to check for.
     */
    private $content;
}
