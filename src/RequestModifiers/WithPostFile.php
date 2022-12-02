<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\RequestModifiers;

use DataTypes\System\FilePathInterface;
use MichaelHall\HttpClient\HttpClientRequestInterface;
use MichaelHall\Webunit\Exceptions\EmptyParameterNameException;
use MichaelHall\Webunit\Exceptions\FileNotFoundException;
use MichaelHall\Webunit\Exceptions\InvalidFilePathException;
use MichaelHall\Webunit\Interfaces\RequestModifierInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;

/**
 * Class representing a request modifier for attaching a POST file.
 *
 * @since 2.1.0
 */
class WithPostFile implements RequestModifierInterface
{
    /**
     * WithPostFile constructor.
     *
     * @since 2.1.0
     *
     * @param string            $parameterName The name of the POST parameter.
     * @param FilePathInterface $filePath      The absolute path to the POST file.
     *
     * @throws EmptyParameterNameException If the name of the POST parameter is an empty string.
     * @throws InvalidFilePathException    If the file path is not an absolute path to a file.
     * @throws FileNotFoundException       If the file could not be found.
     */
    public function __construct(string $parameterName, FilePathInterface $filePath)
    {
        if ($parameterName === '') {
            throw new EmptyParameterNameException('POST parameter name is empty');
        }

        if (!$filePath->isAbsolute() || !$filePath->isFile()) {
            throw new InvalidFilePathException('File path "' . $filePath . '" must be absolute path to a file');
        }

        if (!file_exists($filePath->__toString())) {
            throw new FileNotFoundException('File "' . $filePath . '" was not found');
        }

        $this->parameterName = $parameterName;
        $this->filePath = $filePath;
    }

    /**
     * Returns the name of the POST parameter.
     *
     * @since 2.1.0
     *
     * @return string The name of the POST parameter.
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * Returns the absolute path to the POST file.
     *
     * @since 2.1.0
     *
     * @return FilePathInterface The absolute path to the POST file.
     */
    public function getFilePath(): FilePathInterface
    {
        return $this->filePath;
    }

    /**
     * Checks if this request modifier is allowed to use for the specified request method.
     *
     * @since 2.1.0
     *
     * @param string $method The request method.
     *
     * @return bool True if request modifier is allowed, false otherwise.
     */
    public function isAllowedForMethod(string $method): bool
    {
        return $method !== TestCaseInterface::METHOD_GET;
    }

    /**
     * Checks if this request modifier is compatible with (i.e. can exist on the same test case) as another request modifier.
     *
     * @since 2.1.0
     *
     * @param RequestModifierInterface $requestModifier The other request modifier.
     *
     * @return bool True if request modifiers are compatible, false otherwise.
     */
    public function isCompatibleWith(RequestModifierInterface $requestModifier): bool
    {
        if ($requestModifier instanceof WithRawContent) {
            return false;
        }

        return true;
    }

    /**
     * Modifies a request.
     *
     * @since 2.1.0
     *
     * @param HttpClientRequestInterface $request
     */
    public function modifyRequest(HttpClientRequestInterface $request): void
    {
        $request->setFile($this->parameterName, $this->filePath);
    }

    /**
     * @var string The name of the POST parameter.
     */
    private string $parameterName;

    /**
     * @var FilePathInterface The absolute path to the POST file.
     */
    private FilePathInterface $filePath;
}
