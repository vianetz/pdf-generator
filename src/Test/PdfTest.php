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
use Vianetz\Pdf\Model\Config;
use Vianetz\Pdf\Model\Generator\AbstractGenerator;
use Vianetz\Pdf\Model\HtmlDocument;
use Vianetz\Pdf\Model\PdfFactory;
use Vianetz\Pdf\NoDataException;

final class PdfTest extends TestCase
{
    private const TMP_DIR = './tmp_dir/';

    private function getDocumentMock(): HtmlDocument
    {
        /** @var \Vianetz\Pdf\Model\HtmlDocument $document */
        $document = new HtmlDocument('<html><body>This is the <strong>pdf-generator</strong> test!</body></html>');

        return $document;
    }

    private function getPdfMock(?Config $config = null): \Vianetz\Pdf\Model\Pdf
    {
        return PdfFactory::general()->create($config);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Remove debug file if existent
        @unlink(\Vianetz\Pdf\Model\Generator\AbstractGenerator::DEBUG_FILE_NAME);
        @rmdir(self::TMP_DIR);
    }

    public function testAddOneDocumentIncreasesDocumentCounterByOne(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->addDocument($this->getDocumentMock());

        $this->assertEquals(1, $pdfMock->countDocuments());
    }

    public function testAddThreeDocumentsIncreasesDocumentCounterByThree(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->addDocument($this->getDocumentMock())
            ->addDocument($this->getDocumentMock())
            ->addDocument($this->getDocumentMock());

        $this->assertEquals(3, $pdfMock->countDocuments());
    }

    public function testGetContentsReturnsExceptionIfNoDocumentsAdded(): void
    {
        $this->expectException(NoDataException::class);
        $this->getPdfMock()->getContents();
    }

    public function testGetContentsReturnsNonEmptyResult(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->addDocument($this->getDocumentMock());

        $this->assertNotEmpty($pdfMock->getContents());
    }

    public function testDebugModeGeneratesDebugFile(): void
    {
        $config = new Config();
        $config->setIsDebugMode(true)
            ->setTempDir('.');

        $pdfMock = $this->getPdfMock($config);
        $pdfMock->addDocument($this->getDocumentMock())
            ->render();
        $this->assertFileExists(AbstractGenerator::DEBUG_FILE_NAME);
    }

    public function testConfigTempDirMayNotBeNull(): void
    {
        $config = new Config();

        $this->assertNotEmpty($config->getTempDir());
    }

    public function testNoExceptionIfTempDirNotWritable(): void
    {
        @mkdir(self::TMP_DIR, 0000);

        $config = new Config();
        $config->setTempDir(self::TMP_DIR);

        $pdfMock = $this->getPdfMock($config);
        $pdfMock->addDocument($this->getDocumentMock())
            ->render();

        $this->assertDirectoryIsNotWritable(self::TMP_DIR);
    }
}