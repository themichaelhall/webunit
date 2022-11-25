<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\RequestModifiers;

use DataTypes\Net\Url;
use DataTypes\System\FilePath;
use MichaelHall\HttpClient\HttpClientRequest;
use MichaelHall\Webunit\Exceptions\EmptyParameterNameException;
use MichaelHall\Webunit\Exceptions\FileNotFoundException;
use MichaelHall\Webunit\Exceptions\InvalidFilePathException;
use MichaelHall\Webunit\RequestModifiers\WithPostFile;
use MichaelHall\Webunit\RequestModifiers\WithPostParameter;
use MichaelHall\Webunit\RequestModifiers\WithRawContent;
use PHPUnit\Framework\TestCase;

/**
 * Test WithPostFile class.
 */
class WithPostFileTest extends TestCase
{
    /**
     * Test getParameterName method.
     */
    public function testGetParameterName()
    {
        $withPostFile = new WithPostFile('Foo', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt'));

        self::assertSame('Foo', $withPostFile->getParameterName());
    }

    /**
     * Test getFilePath method.
     */
    public function testGetFilePath()
    {
        $withPostFile = new WithPostFile('Foo', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt'));

        self::assertTrue(FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt')->equals($withPostFile->getFilePath()));
    }

    /**
     * Test modifyRequest method.
     */
    public function testModifyRequest()
    {
        $request = new HttpClientRequest(Url::parse('https://example.com/'));

        $withPostFile = new WithPostFile('Foo', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt'));
        $withPostFile->modifyRequest($request);

        self::assertCount(1, $request->getFiles());
        self::assertTrue(FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt')->equals($request->getFiles()['Foo']));
    }

    /**
     * Test create request modifier with an empty parameter name.
     */
    public function testWithEmptyParameterName()
    {
        self::expectException(EmptyParameterNameException::class);
        self::expectExceptionMessage('POST parameter name is empty');

        new WithPostFile('', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt'));
    }

    /**
     * Test create request modifier with a relative file path.
     */
    public function testWithRelativeFilePath()
    {
        self::expectException(InvalidFilePathException::class);
        self::expectExceptionMessage('File path "' . FilePath::parse('../Helpers/TestFiles/helloworld.txt') . '" must be absolute path to a file');

        new WithPostFile('Foo', FilePath::parse('../Helpers/TestFiles/helloworld.txt'));
    }

    /**
     * Test create request modifier with a file path to a directory.
     */
    public function testWithDirectoryFilePath()
    {
        self::expectException(InvalidFilePathException::class);
        self::expectExceptionMessage('File path "' . FilePath::parse(__DIR__ . '/../Helpers/TestFiles/') . '" must be absolute path to a file');

        new WithPostFile('Foo', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/'));
    }

    /**
     * Test create request modifier with non-existing file.
     */
    public function testWithNonExistingFile()
    {
        self::expectException(FileNotFoundException::class);
        self::expectExceptionMessage('File "' . FilePath::parse(__DIR__ . '/../Helpers/TestFiles/notfound.txt') . '" was not found');

        new WithPostFile('Foo', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/notfound.txt'));
    }

    /**
     * Test isCompatibleWith method.
     */
    public function testIsCompatibleWith()
    {
        $withPostFile = new WithPostFile('Foo', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt'));

        self::assertTrue($withPostFile->isCompatibleWith(new WithPostFile('Bar', FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt'))));
        self::assertTrue($withPostFile->isCompatibleWith(new WithPostParameter('Bar', 'Baz')));
        self::assertFalse($withPostFile->isCompatibleWith(new WithRawContent('{"Baz": true}')));
    }
}
