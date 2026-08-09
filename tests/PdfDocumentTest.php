<?php
declare(strict_types=1);

/**
 * @section LICENSE
 * This file is created by vianetz <info@vianetz.com>.
 * The code is distributed under the GPL license.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@vianetz.com so we can send you a copy immediately.
 *
 * @package     Vianetz\Pdf
 * @author      Christoph Massmann, <cm@vianetz.com>
 * @link        https://www.vianetz.com
 * @copyright   Copyright (c) since 2006 vianetz - Dipl.-Ing. C. Massmann (https://www.vianetz.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE
 */

namespace Vianetz\Pdf\Test;

use PHPUnit\Framework\TestCase;
use Vianetz\Pdf\FileNotFoundException;
use Vianetz\Pdf\Model\PdfDocument;

final class PdfDocumentTest extends TestCase
{
    use TempFiles;

    public function tearDown(): void
    {
        parent::tearDown();

        $this->tearDownTempFiles();
    }

    public function testExistingFileIsReadIntoTheDocument(): void
    {
        $fileName = $this->createTempFile('%PDF-1.4 test contents');

        $this->assertEquals('%PDF-1.4 test contents', (new PdfDocument($fileName))->toPdf());
    }

    /** @return array<string, array{0: string}> */
    public static function invalidFileNameDataProvider(): array
    {
        return [
            'non-existent file' => [__DIR__ . '/this-file-does-not-exist.pdf'],
            'empty file name' => [''],
            'file name is only whitespace' => ['   '],
        ];
    }

    /** @dataProvider invalidFileNameDataProvider */
    public function testInvalidFileNameThrowsFileNotFoundException(string $fileName): void
    {
        $this->expectException(FileNotFoundException::class);

        new PdfDocument($fileName);
    }

    public function testDirectoryThrowsFileNotFoundException(): void
    {
        $dirName = $this->createTempDir();

        $this->expectException(FileNotFoundException::class);

        new PdfDocument($dirName);
    }

    public function testExceptionMessageContainsTheFileName(): void
    {
        $fileName = __DIR__ . '/this-file-does-not-exist.pdf';

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote($fileName, '/') . '/');

        new PdfDocument($fileName);
    }
}
