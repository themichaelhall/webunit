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

/**
 * Class representing an assertion for containing test content.
 *
 * @since 1.0.0
 */
class AssertContains extends AbstractAssert
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
        parent::__construct();

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
            mb_stristr($pageResult->getContent(), $this->content) !== false :
            strpos($pageResult->getContent(), $this->content) !== false;
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
        return 'Content "' . $pageResult->getContent() . '" ' . ($this->getModifiers()->isNot() ? 'contains' : 'does not contain') . ' "' . $this->content . '"';
    }

    /**
     * @var string My content to check for.
     */
    private $content;
}
