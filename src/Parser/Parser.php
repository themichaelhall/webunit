<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Parser;

use DataTypes\Interfaces\FilePathInterface;
use DataTypes\Url;
use MichaelHall\Webunit\Interfaces\ParseResultInterface;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\TestCase;
use MichaelHall\Webunit\TestSuite;

/**
 * Class representing a parser.
 *
 * @since 1.0.0
 */
class Parser
{
    /**
     * Parses content into a test suite.
     *
     * @since 1.0.0
     *
     * @param FilePathInterface $filePath The file path.
     * @param string[]          $content  The content.
     *
     * @return ParseResultInterface The parse result.
     */
    public function parse(FilePathInterface $filePath, array $content): ParseResultInterface
    {
        $testSuite = new TestSuite();
        $parseErrors = [];

        $lineNumber = 0;

        foreach ($content as $line) {
            $line = trim($line);
            $lineNumber++;

            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $lineParts = preg_split('/\s+/', $line, 2);
            $command = trim($lineParts[0]);
            if (strtolower($command) !== 'get') {
                $parseErrors[] = new ParseError(new FileLocation($filePath, $lineNumber), 'Syntax error: Invalid command "' . $command . '".');

                continue;
            }

            $parameter = trim($lineParts[1]);

            $testCase = new TestCase(Url::parse($parameter));
            $testSuite->addTestCase($testCase);
        }

        return new ParseResult($testSuite, $parseErrors);
    }
}
