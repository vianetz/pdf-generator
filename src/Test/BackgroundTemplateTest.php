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
use Vianetz\Pdf\Model\HtmlDocument;
use Vianetz\Pdf\Model\Merger\Fpdf;
use Vianetz\Pdf\Model\MergerInterface;
use Vianetz\Pdf\Model\PdfFactory;
use Vianetz\Pdf\Model\PdfMerge;

/** Stamping every page onto a template pdf, i.e. the letterhead use case. */
final class BackgroundTemplateTest extends TestCase
{
    private const PDF_STRING = '%PDF-1.4 does not matter, the merger is mocked';

    /** @var list<string> */
    private array $tmpFiles = [];

    public function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->tmpFiles as $tmpFile) {
            @unlink($tmpFile);
        }
        $this->tmpFiles = [];
    }

    /** Renders a single page pdf containing the marker and returns the file it was written to. */
    private function createTemplateFile(string $marker): string
    {
        $fileName = (string) tempnam(sys_get_temp_dir(), 'vianetz-pdf-test-template');
        $this->tmpFiles[] = $fileName;

        $pdf = PdfFactory::general()->create();
        $pdf->add(new HtmlDocument('<html><body>' . $marker . '</body></html>'));
        $pdf->saveToFile($fileName);

        return $fileName;
    }

    /**
     * A merger recording the background templates it is asked to import, one entry per page.
     *
     * @param list<string> $importedTemplates
     */
    private function createRecordingMerger(int $pageCount, array &$importedTemplates): MergerInterface
    {
        $merger = $this->createMock(MergerInterface::class);
        $merger->method('countPages')->willReturn($pageCount);
        $merger->method('addPage')->willReturnSelf();
        $merger->method('toPdf')->willReturn('%PDF-1.4');
        $merger->method('importBackgroundTemplateFile')
            ->willReturnCallback(static function (string $pdfBackgroundFile) use (&$importedTemplates): void {
                $importedTemplates[] = $pdfBackgroundFile;
            });

        return $merger;
    }

    public function testBackgroundFileIsImportedForEveryPage(): void
    {
        $importedTemplates = [];
        $pdfMerge = PdfMerge::create($this->createRecordingMerger(3, $importedTemplates));

        $pdfMerge->mergePdfString(self::PDF_STRING, 'background.pdf');

        $this->assertEquals(['background.pdf', 'background.pdf', 'background.pdf'], $importedTemplates);
    }

    public function testDedicatedFirstPageBackgroundIsUsedForTheFirstPageOnly(): void
    {
        $importedTemplates = [];
        $pdfMerge = PdfMerge::create($this->createRecordingMerger(3, $importedTemplates));

        $pdfMerge->mergePdfString(self::PDF_STRING, 'background.pdf', 'background-first-page.pdf');

        $this->assertEquals(['background-first-page.pdf', 'background.pdf', 'background.pdf'], $importedTemplates);
    }

    /** The behaviour introduced in 5.0.0, reversing what 1.0.3 did. */
    public function testGeneralBackgroundIsAlsoUsedForTheFirstPageIfNoDedicatedOneIsSet(): void
    {
        $importedTemplates = [];
        $pdfMerge = PdfMerge::create($this->createRecordingMerger(2, $importedTemplates));

        $pdfMerge->mergePdfString(self::PDF_STRING, 'background.pdf', null);

        $this->assertEquals(['background.pdf', 'background.pdf'], $importedTemplates);
    }

    public function testFirstPageBackgroundAloneLeavesTheRemainingPagesUnstamped(): void
    {
        $importedTemplates = [];
        $pdfMerge = PdfMerge::create($this->createRecordingMerger(3, $importedTemplates));

        $pdfMerge->mergePdfString(self::PDF_STRING, null, 'background-first-page.pdf');

        $this->assertEquals(['background-first-page.pdf'], $importedTemplates);
    }

    public function testNoBackgroundIsImportedIfTheDocumentHasNone(): void
    {
        $importedTemplates = [];
        $pdfMerge = PdfMerge::create($this->createRecordingMerger(2, $importedTemplates));

        $pdfMerge->mergePdfString(self::PDF_STRING);

        $this->assertEquals([], $importedTemplates);
    }

    public function testBackgroundIsStampedOntoTheRenderedDocument(): void
    {
        $backgroundFile = $this->createTemplateFile('LETTERHEAD-MARKER');

        $pdf = PdfFactory::general()->create();
        $pdf->add(new HtmlDocument('<html><body>BODY-MARKER</body></html>', $backgroundFile));

        $pdfContents = $pdf->toPdf();

        $this->assertEquals(1, PdfContents::pageCount($pdfContents));
        $this->assertStringContainsString('LETTERHEAD-MARKER', PdfContents::text($pdfContents));
        $this->assertStringContainsString('BODY-MARKER', PdfContents::text($pdfContents));
    }

    /** Which template landed on which page is pinned by the mock tests - {@see PdfContents::text()} cannot tell. */
    public function testBothTemplatesEndUpInADocumentWithADedicatedFirstPage(): void
    {
        $backgroundFile = $this->createTemplateFile('LETTERHEAD-MARKER');
        $firstPageBackgroundFile = $this->createTemplateFile('FIRST-PAGE-MARKER');

        $html = '<html><body><div style="page-break-after: always;">PAGE-ONE</div><div>PAGE-TWO</div></body></html>';

        $pdf = PdfFactory::general()->create();
        $pdf->add(new HtmlDocument($html, $backgroundFile, $firstPageBackgroundFile));

        $pdfContents = $pdf->toPdf();

        $this->assertEquals(2, PdfContents::pageCount($pdfContents));
        $this->assertStringContainsString('FIRST-PAGE-MARKER', PdfContents::text($pdfContents));
        $this->assertStringContainsString('LETTERHEAD-MARKER', PdfContents::text($pdfContents));
    }

    /** Rendering deliberately fails instead of quietly continuing without the letterhead, see 5.0.0. */
    public function testMissingBackgroundFileThrowsFileNotFoundException(): void
    {
        $pdf = PdfFactory::general()->create();
        $pdf->add(new HtmlDocument('<html><body>BODY-MARKER</body></html>', __DIR__ . '/this-background-does-not-exist.pdf'));

        $this->expectException(FileNotFoundException::class);

        $pdf->toPdf();
    }

    /** Note the layers disagree: PdfMerge skips an empty name, the merger below rejects it. */
    public function testEmptyBackgroundFileNameIsTreatedAsNoBackground(): void
    {
        $pdf = PdfFactory::general()->create();
        $pdf->add(new HtmlDocument('<html><body>BODY-MARKER</body></html>', ''));

        $this->assertEquals(1, PdfContents::pageCount($pdf->toPdf()));
    }

    public function testMergerRejectsAnEmptyBackgroundTemplateFileName(): void
    {
        $this->expectException(FileNotFoundException::class);

        (new Fpdf())->importBackgroundTemplateFile('');
    }

    public function testMergerRejectsAMissingBackgroundTemplateFile(): void
    {
        $this->expectException(FileNotFoundException::class);

        (new Fpdf())->importBackgroundTemplateFile(__DIR__ . '/this-background-does-not-exist.pdf');
    }

    public function testHtmlDocumentExposesItsBackgroundFiles(): void
    {
        $document = new HtmlDocument('<html/>', 'background.pdf', 'background-first-page.pdf');

        $this->assertEquals('background.pdf', $document->getPdfBackgroundFile());
        $this->assertEquals('background-first-page.pdf', $document->getPdfBackgroundFileForFirstPage());
    }

    public function testHtmlDocumentHasNoBackgroundFilesByDefault(): void
    {
        $document = new HtmlDocument('<html/>');

        $this->assertNull($document->getPdfBackgroundFile());
        $this->assertNull($document->getPdfBackgroundFileForFirstPage());
    }
}
