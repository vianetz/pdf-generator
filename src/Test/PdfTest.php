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
 * @category    Vianetz
 * @package     Vianetz\Pdf
 * @author      Christoph Massmann, <C.Massmann@vianetz.com>
 * @link        http://www.vianetz.com
 * @copyright   Copyright (c) since 2006 vianetz - Dipl.-Ing. C. Massmann (http://www.vianetz.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE
 */

namespace Vianetz\Pdf\Test;

use Vianetz\Pdf\Model\Config;
use PHPUnit\Framework\TestCase;
use Vianetz\Pdf\Model\EventManagerInterface;
use Vianetz\Pdf\Model\Generator\AbstractGenerator;
use Vianetz\Pdf\Model\PdfFactory;
use Vianetz\Pdf\NoDataException;

class PdfTest extends TestCase
{
    private function getDocumentMock()
    {
        $document = new \Vianetz\Pdf\Model\Document();
        $document->setHtmlContents('<html>test</html>');

        return $document;
    }

    private function getPdfMock(Config $config)
    {
        $eventManagerMock = $this->createMock(EventManagerInterface::class);

        return PdfFactory::general()->create($config, $eventManagerMock);
    }

    public function tearDown()
    {
        parent::tearDown();

        // Remove debug file if existent
        @unlink(\Vianetz\Pdf\Model\Generator\AbstractGenerator::DEBUG_FILE_NAME);
        @rmdir('./tmp/');
    }

    public function testAddOneDocumentIncreasesDocumentCounterByOne()
    {
        $pdfMock = $this->getPdfMock(new Config());
        $pdfMock->addDocument($this->getDocumentMock());

        $this->assertEquals(1, $pdfMock->countDocuments());
    }

    public function testAddThreeDocumentsIncreasesDocumentCounterByThree()
    {
        $pdfMock = $this->getPdfMock(new Config());
        $pdfMock->addDocument($this->getDocumentMock())
            ->addDocument($this->getDocumentMock())
            ->addDocument($this->getDocumentMock());

        $this->assertEquals(3, $pdfMock->countDocuments());
    }

    public function testGetContentsReturnsExceptionIfNoDocumentsAdded()
    {
        $this->expectException(NoDataException::class);
        $this->getPdfMock(new Config())->getContents();
    }

    public function testGetContentsReturnsExpectedResult()
    {
        $pdfMock = $this->getPdfMock(new Config());
        $pdfMock->addDocument($this->getDocumentMock());

        $this->assertNotEmpty($pdfMock->getContents());
    }

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

    public function testConfigTempDirMayNotBeNull()
    {
        $config = new Config();

        $this->assertNotEmpty($config->getTempDir());
    }

    public function testExceptionIfTempDirNotWritable()
    {
        @mkdir('./tmp', 0000);

        $this->expectException(\Vianetz\Pdf\Exception::class);

        $config = new Config();
        $config->setTempDir('tmp/');

        $pdfMock = $this->getPdfMock($config);
        $pdfMock->addDocument($this->getDocumentMock())
            ->render();
    }
}