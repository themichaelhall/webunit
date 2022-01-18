<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions\Base;

use MichaelHall\Webunit\Exceptions\InvalidRegexpException;
use MichaelHall\Webunit\Exceptions\NotAllowedModifierException;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;

/**
 * Abstract assertion with content base class.
 *
 * @since 1.0.0
 */
abstract class AbstractContentAssert extends AbstractAssert
{
    /**
     * Creates an abstract assert with content.
     *
     * @since 1.0.0
     *
     * @param LocationInterface  $location  The location.
     * @param string             $content   The content.
     * @param ModifiersInterface $modifiers The modifiers.
     *
     * @throws InvalidRegexpException      If modifiers contains regexp and content is not a valid regexp.
     * @throws NotAllowedModifierException If modifiers are not allowed for this assert.
     */
    public function __construct(LocationInterface $location, string $content, ModifiersInterface $modifiers)
    {
        parent::__construct($location, $modifiers);

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        if ($modifiers->isRegexp() && @preg_match('/' . $content . '/', '') === false) {
            throw new InvalidRegexpException($content);
        }

        $this->content = $content;
    }

    /**
     * Returns the content.
     *
     * @since 1.0.0
     *
     * @return string The content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @var string My content.
     */
    private $content;
}
