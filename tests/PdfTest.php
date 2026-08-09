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
use Vianetz\Pdf\InvalidDocumentException;
use Vianetz\Pdf\Model\Config;
use Vianetz\Pdf\Model\EventManagerInterface;
use Vianetz\Pdf\Model\Generator\AbstractGenerator;
use Vianetz\Pdf\Model\Generator\Dompdf;
use Vianetz\Pdf\Model\HtmlDocument;
use Vianetz\Pdf\Model\Merger\Fpdf;
use Vianetz\Pdf\Model\Merger\Fpdi;
use Vianetz\Pdf\Model\MergerInterface;
use Vianetz\Pdf\Model\NoneEventManager;
use Vianetz\Pdf\Model\Pdfable;
use Vianetz\Pdf\Model\PdfFactory;
use Vianetz\Pdf\NoDataException;

final class PdfTest extends TestCase
{
    use TempFiles;

    private function getDocumentMock(): HtmlDocument
    {
        /** @var \Vianetz\Pdf\Model\HtmlDocument $document */
        $document = new HtmlDocument('<html><body>This is the <strong>pdf-generator</strong> test!</body></html>');

        return $document;
    }

    private function getDocumentWithMarker(string $marker): HtmlDocument
    {
        return new HtmlDocument('<html><body>' . $marker . '</body></html>');
    }

    private function getPdfMock(?Config $config = null): \Vianetz\Pdf\Model\Pdf
    {
        return PdfFactory::general()->create($config);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->tearDownTempFiles();
    }

    public function testAddOneDocumentIncreasesDocumentCounterByOne(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->add($this->getDocumentMock());

        $this->assertEquals(1, $pdfMock->countDocuments());
    }

    public function testAddThreeDocumentsIncreasesDocumentCounterByThree(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->add($this->getDocumentMock())
            ->add($this->getDocumentMock())
            ->add($this->getDocumentMock());

        $this->assertEquals(3, $pdfMock->countDocuments());
    }

    public function testGetContentsReturnsExceptionIfNoDocumentsAdded(): void
    {
        $this->expectException(NoDataException::class);
        $this->getPdfMock()->toPdf();
    }

    public function testGetContentsReturnsNonEmptyResult(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->add($this->getDocumentMock());

        $this->assertNotEmpty($pdfMock->toPdf());
    }

    public function testDebugModeGeneratesDebugFile(): void
    {
        $tempDir = $this->createTempDir();

        $config = new Config();
        $config->setIsDebugMode(true)
            ->setTempDir($tempDir);

        $pdfMock = $this->getPdfMock($config);
        $pdfMock->add($this->getDocumentMock())
            ->render();

        $this->assertFileExists($tempDir . DIRECTORY_SEPARATOR . AbstractGenerator::DEBUG_FILE_NAME);
    }

    public function testNoDebugFileIsWrittenIfDebugModeIsOff(): void
    {
        $tempDir = $this->createTempDir();

        $config = new Config();
        $config->setTempDir($tempDir);

        $pdfMock = $this->getPdfMock($config);
        $pdfMock->add($this->getDocumentMock())
            ->render();

        $this->assertFileDoesNotExist($tempDir . DIRECTORY_SEPARATOR . AbstractGenerator::DEBUG_FILE_NAME);
    }

    public function testConfigTempDirMayNotBeNull(): void
    {
        $config = new Config();

        $this->assertNotEmpty($config->getTempDir());
    }

    /**
     * A temp dir that cannot be written to must not stop a document from being rendered - the debug file
     * and the font cache are best effort.
     *
     * The unwritable path is one that does not exist rather than one made unwritable by its mode, because
     * root ignores the mode bits and would silently turn this into a test of nothing.
     */
    public function testNoExceptionIfTempDirNotWritable(): void
    {
        $unwritablePath = $this->unwritablePath();

        $config = new Config();
        $config->setIsDebugMode(true)
            ->setTempDir($unwritablePath);

        $pdfMock = $this->getPdfMock($config);
        $pdfMock->add($this->getDocumentMock());

        // Guard the premise - assertDirectoryIsNotWritable() cannot be used, it requires the directory to exist.
        $this->assertFalse(is_writable($unwritablePath));
        $this->assertNotEmpty($pdfMock->render());
    }

    public function testDocumentIsRenderedInTheConfiguredPaperSize(): void
    {
        $config = new Config();
        $config->setPdfSize('a3');

        $pdfMock = $this->getPdfMock($config);
        $pdfMock->add($this->getDocumentMock());

        $this->assertMatchesRegularExpression('/MediaBox\s*\[\s*0\s+0\s+841\.89\s+1190\.55\s*\]/', $pdfMock->toPdf());
    }

    public function testGetContentsReturnsExceptionIfAddedDocumentIsEmpty(): void
    {
        $emptyDocument = new class implements Pdfable {
            public function toPdf(): string
            {
                return '';
            }
        };

        $pdfMock = $this->getPdfMock();
        $pdfMock->add($emptyDocument);

        $this->expectException(NoDataException::class);
        $pdfMock->toPdf();
    }

    public function testAttachmentIsAddedIfDocumentHasAlreadyBeenRendered(): void
    {
        $config = new Config();

        $merger = $this->createMock(MergerInterface::class);
        $merger->method('countPages')->willReturn(1);
        $merger->method('addPage')->willReturnSelf();
        $merger->method('toPdf')->willReturn('%PDF-1.4');
        // Without resetting the cached contents on attach() the second render is served from the cache
        // and the attachment is silently dropped.
        $merger->expects($this->once())
            ->method('addAttachment')
            ->with('attachment.xml')
            ->willReturnSelf();

        $pdfMock = new \Vianetz\Pdf\Model\Pdf($config, new NoneEventManager(), new Dompdf($config), $merger);
        $pdfMock->add($this->getDocumentMock());
        $pdfMock->toPdf();

        $pdfMock->attach('attachment.xml');
        $pdfMock->toPdf();
    }

    /** Re-rendering must not reuse the previous merger - that failed with "FPDF error: The document is closed". */
    public function testDocumentAddedAfterAFirstRenderIsIncludedInTheSecondRender(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->add($this->getDocumentWithMarker('FIRST-DOCUMENT'));
        $firstRender = $pdfMock->toPdf();

        $pdfMock->add($this->getDocumentWithMarker('SECOND-DOCUMENT'));
        $secondRender = $pdfMock->toPdf();

        $this->assertEquals(1, PdfContents::pageCount($firstRender));
        $this->assertEquals(2, PdfContents::pageCount($secondRender), 'the document added after the first render is missing');
        $this->assertStringContainsString('FIRST-DOCUMENT', PdfContents::text($secondRender));
        $this->assertStringContainsString('SECOND-DOCUMENT', PdfContents::text($secondRender));
    }

    /** Every render starts from scratch, so earlier documents must not show up twice. */
    public function testRepeatedRendersDoNotAccumulatePages(): void
    {
        $pdfMock = $this->getPdfMock();

        $pdfMock->add($this->getDocumentMock());
        $pdfMock->toPdf();
        $pdfMock->add($this->getDocumentMock());
        $pdfMock->toPdf();
        $pdfMock->add($this->getDocumentMock());

        $this->assertEquals(3, PdfContents::pageCount($pdfMock->toPdf()));
    }

    /** Without a mutation in between the second render is served from the cache and merges nothing. */
    public function testRenderingTwiceWithoutChangesIsServedFromTheCache(): void
    {
        $config = new Config();

        $merger = $this->createMock(MergerInterface::class);
        $merger->method('countPages')->willReturn(1);
        $merger->method('addPage')->willReturnSelf();
        $merger->method('toPdf')->willReturn('%PDF-1.4');
        $merger->expects($this->once())->method('importPageFromPdfString');

        $pdfMock = new \Vianetz\Pdf\Model\Pdf($config, new NoneEventManager(), new Dompdf($config), $merger);
        $pdfMock->add($this->getDocumentMock());

        $this->assertEquals($pdfMock->toPdf(), $pdfMock->toPdf());
    }

    /** Re-rendering produces the same document, not a longer one. */
    public function testRenderingAgainAfterAMutationIsStableForTheUnchangedDocuments(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->add($this->getDocumentWithMarker('FIRST-DOCUMENT'));
        $pdfMock->toPdf();
        $pdfMock->add($this->getDocumentWithMarker('SECOND-DOCUMENT'));

        $referenceMock = $this->getPdfMock();
        $referenceMock->add($this->getDocumentWithMarker('FIRST-DOCUMENT'))
            ->add($this->getDocumentWithMarker('SECOND-DOCUMENT'));

        $this->assertEquals(
            PdfContents::pageCount($referenceMock->toPdf()),
            PdfContents::pageCount($pdfMock->toPdf())
        );
    }

    /** The same check for the tcpdf merger, whose destructor makes it the riskiest clone target. */
    public function testDocumentAddedAfterAFirstRenderIsIncludedWhenMergingWithTcpdf(): void
    {
        if (! class_exists(\TCPDF::class)) {
            self::markTestSkipped('the suggested package tecnickcom/tcpdf is not installed');
        }

        $config = new Config();
        $pdfMock = new \Vianetz\Pdf\Model\Pdf($config, new NoneEventManager(), new Dompdf($config), new Fpdi($config));

        $pdfMock->add($this->getDocumentWithMarker('FIRST-DOCUMENT'));
        $this->assertEquals(1, PdfContents::pageCount($pdfMock->toPdf()));

        $pdfMock->add($this->getDocumentWithMarker('SECOND-DOCUMENT'));
        $this->assertEquals(2, PdfContents::pageCount($pdfMock->toPdf()));
    }

    public function testMergerGivenToTheConstructorIsNotConsumedByARender(): void
    {
        $config = new Config();
        $merger = new Fpdf($config);

        $pdfMock = new \Vianetz\Pdf\Model\Pdf($config, new NoneEventManager(), new Dompdf($config), $merger);
        $pdfMock->add($this->getDocumentMock());
        $pdfMock->toPdf();

        // The merger we handed in stays untouched and may still be used for a document of its own.
        $merger->addPage();
        $this->assertEquals(1, PdfContents::pageCount($merger->toPdf()));
    }

    /** {@see PdfContents::text()} cannot tell the pages apart, so this checks no document got lost, not where it is. */
    public function testEveryDocumentGetsItsOwnPageAndKeepsItsContents(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->add($this->getDocumentWithMarker('DOCUMENT-ONE'))
            ->add($this->getDocumentWithMarker('DOCUMENT-TWO'))
            ->add($this->getDocumentWithMarker('DOCUMENT-THREE'));

        $pdfContents = $pdfMock->toPdf();

        $this->assertEquals(3, PdfContents::pageCount($pdfContents));
        foreach (['DOCUMENT-ONE', 'DOCUMENT-TWO', 'DOCUMENT-THREE'] as $marker) {
            $this->assertStringContainsString($marker, PdfContents::text($pdfContents));
        }
    }

    public function testMultiPageDocumentKeepsAllOfItsPages(): void
    {
        $html = '<html><body>'
            . '<div style="page-break-after: always;">PAGE-ONE</div>'
            . '<div style="page-break-after: always;">PAGE-TWO</div>'
            . '<div>PAGE-THREE</div>'
            . '</body></html>';

        $pdfMock = $this->getPdfMock();
        $pdfMock->add(new HtmlDocument($html));

        $this->assertEquals(3, PdfContents::pageCount($pdfMock->toPdf()));
    }

    public function testSaveToFileWritesTheRenderedContents(): void
    {
        $fileName = $this->createTempFile();

        $pdfMock = $this->getPdfMock();
        $pdfMock->add($this->getDocumentMock());

        $this->assertTrue($pdfMock->saveToFile($fileName));
        $this->assertStringEqualsFile($fileName, $pdfMock->toPdf());
    }

    public function testSaveToFileReturnsFalseIfTheFileCannotBeWritten(): void
    {
        $pdfMock = $this->getPdfMock();
        $pdfMock->add($this->getDocumentMock());

        $this->assertFalse($pdfMock->saveToFile(__DIR__ . '/this-directory-does-not-exist/test.pdf'));
    }

    public function testInvalidDocumentTypeThrowsInvalidDocumentException(): void
    {
        $pdfMock = $this->getPdfMock();
        // Anything that is neither Htmlable nor Pdfable is rejected - but only once it is rendered.
        /** @phpstan-ignore argument.type */
        $pdfMock->add(new \stdClass());

        $this->expectException(InvalidDocumentException::class);

        $pdfMock->toPdf();
    }

    public function testGetConfigReturnsTheConfigTheInstanceWasCreatedWith(): void
    {
        $config = new Config();

        $this->assertSame($config, $this->getPdfMock($config)->getConfig());
    }

    public function testEventsAreDispatchedForEveryDocumentAndForTheContents(): void
    {
        $eventManager = new class implements EventManagerInterface {
            /** @var list<string> */
            private array $dispatchedEvents = [];

            /** {@inheritDoc} */
            public function dispatch(string $eventName, array $data = []): void
            {
                $this->dispatchedEvents[] = $eventName;
            }

            /** @return list<string> */
            public function getDispatchedEvents(): array
            {
                return $this->dispatchedEvents;
            }
        };

        $config = new Config();
        $pdfMock = new \Vianetz\Pdf\Model\Pdf($config, $eventManager, new Dompdf($config), new Fpdf($config));
        $pdfMock->add($this->getDocumentMock())->add($this->getDocumentMock());
        $pdfMock->toPdf();

        $this->assertEquals([
            'vianetz_pdf_document_render_before',
            'vianetz_pdf_document_render_after',
            'vianetz_pdf_document_render_before',
            'vianetz_pdf_document_render_after',
            'vianetz_pdf_get_contents',
        ], $eventManager->getDispatchedEvents());
    }
}
