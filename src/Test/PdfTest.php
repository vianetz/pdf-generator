<?php
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
use Vianetz\Pdf\Model\PdfFactory;
use Vianetz\Pdf\NoDataException;

final class PdfTest extends TestCase
{
    /**
     * @return \Vianetz\Pdf\Model\Document
     */
    private function getDocumentMock()
    {
        /** @var \Vianetz\Pdf\Model\Document $document */
        $document = new \Vianetz\Pdf\Model\Document();
        $document->setHtmlContents('<html>test</html>');

        return $document;
    }

    /**
     * @return \Vianetz\Pdf\Model\Pdf
     */
    private function getPdfMock(Config $config = null)
    {
        return PdfFactory::general()->create($config);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        // Remove debug file if existent
        @unlink(\Vianetz\Pdf\Model\Generator\AbstractGenerator::DEBUG_FILE_NAME);
        @rmdir('./tmp/');
    }

    /**
     * @return void
     */
    public function testAddOneDocumentIncreasesDocumentCounterByOne()
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->addDocument($this->getDocumentMock());

        $this->assertEquals(1, $pdfMock->countDocuments());
    }

    /**
     * @return void
     */
    public function testAddThreeDocumentsIncreasesDocumentCounterByThree()
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->addDocument($this->getDocumentMock())
            ->addDocument($this->getDocumentMock())
            ->addDocument($this->getDocumentMock());

        $this->assertEquals(3, $pdfMock->countDocuments());
    }

    /**
     * @return void
     */
    public function testGetContentsReturnsExceptionIfNoDocumentsAdded()
    {
        $this->expectException(NoDataException::class);
        $this->getPdfMock()->getContents();
    }

    /**
     * @return void
     */
    public function testGetContentsReturnsExpectedResult()
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->addDocument($this->getDocumentMock());

        $this->assertNotEmpty($pdfMock->getContents());
    }

    /**
     * @return void
     */
    public function testDebugModeGeneratesDebugFile()
    {
        $config = new Config();
        $config->setIsDebugMode(true)
            ->setTempDir('.');

        $pdfMock = $this->getPdfMock($config);
        $pdfMock->addDocument($this->getDocumentMock())
            ->render();
        $this->assertFileExists(AbstractGenerator::DEBUG_FILE_NAME);
    }

    /**
     * @return void
     */
    public function testConfigTempDirMayNotBeNull()
    {
        $config = new Config();

        $this->assertNotEmpty($config->getTempDir());
    }

    /**
     * @return void
     */
    public function testNoExceptionIfTempDirNotWritable()
    {
        @mkdir('./tmp', 0000);

        $config = new Config();
        $config->setTempDir('tmp/');

        $pdfMock = $this->getPdfMock($config);
        $pdfMock->addDocument($this->getDocumentMock())
            ->render();

        $this->assertDirectoryNotIsWritable('tmp/');
    }
}