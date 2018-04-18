<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Parser;

use DataTypes\Url;
use MichaelHall\Webunit\Interfaces\TestSuiteInterface;
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
     * @param string[] $content The content.
     *
     * @return TestSuiteInterface The test suite.
     */
    public function parse(array $content): TestSuiteInterface
    {
        $result = new TestSuite();

        foreach ($content as $line) {
            $line = trim($line);
            $lineParts = preg_split('/\s+/', $line, 2);

            $testCase = new TestCase(Url::parse($lineParts[1]));
            $result->addTestCase($testCase);
        }

        return $result;
    }
}
