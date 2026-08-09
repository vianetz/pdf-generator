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
use Vianetz\Pdf\Model\HtmlDocument;
use Vianetz\Pdf\Model\Merger\Fpdf;
use Vianetz\Pdf\Model\PdfFactory;
use Vianetz\Pdf\Model\PdfMerge;
use Vianetz\Pdf\NoDataException;

/** Merging existing pdf contents without going through a generator, i.e. {@see \Vianetz\Pdf\Model\PdfMerge}. */
final class PdfMergeTest extends TestCase
{
    private function getPdfMergeMock(): PdfMerge
    {
        return PdfMerge::create();
    }

    /** Renders a pdf of the given page count, each page carrying "<marker>-<page number>". */
    private function createPdfString(string $marker, int $pageCount = 1): string
    {
        $html = '<html><body>';
        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $pageBreak = $pageNumber < $pageCount ? ' style="page-break-after: always;"' : '';
            $html .= '<div' . $pageBreak . '>' . $marker . '-' . $pageNumber . '</div>';
        }

        $pdf = PdfFactory::general()->create();
        $pdf->add(new HtmlDocument($html . '</body></html>'));

        return $pdf->toPdf();
    }

    public function testMergeGeneratesEmptyPdfIfNoContentsAdded(): void
    {
        $pdfMergeMock = $this->getPdfMergeMock();
        $this->expectException(NoDataException::class);

        $pdfMergeMock->toPdf();
    }

    public function testMergingASingleDocumentKeepsAllOfItsPages(): void
    {
        $pdfMerge = $this->getPdfMergeMock();
        $pdfMerge->mergePdfString($this->createPdfString('SOURCE', 3));

        $this->assertEquals(3, PdfContents::pageCount($pdfMerge->toPdf()));
    }

    public function testMergingTwoDocumentsYieldsTheSumOfTheirPages(): void
    {
        $pdfMerge = $this->getPdfMergeMock();
        $pdfMerge->mergePdfString($this->createPdfString('FIRST', 2));
        $pdfMerge->mergePdfString($this->createPdfString('SECOND', 3));

        $this->assertEquals(5, PdfContents::pageCount($pdfMerge->toPdf()));
    }

    public function testMergedDocumentContainsTheContentsOfEverySource(): void
    {
        $pdfMerge = $this->getPdfMergeMock();
        $pdfMerge->mergePdfString($this->createPdfString('FIRST'));
        $pdfMerge->mergePdfString($this->createPdfString('SECOND'));

        $text = PdfContents::text($pdfMerge->toPdf());

        $this->assertStringContainsString('FIRST-1', $text);
        $this->assertStringContainsString('SECOND-1', $text);
    }

    public function testPagesAreMergedInTheOrderTheyWereAdded(): void
    {
        $pdfMerge = $this->getPdfMergeMock();
        $pdfMerge->mergePdfString($this->createPdfString('FIRST'));
        $pdfMerge->mergePdfString($this->createPdfString('SECOND'));

        $text = PdfContents::text($pdfMerge->toPdf());

        // strpos() returns false for a missing marker, and false is less than any offset.
        $this->assertStringContainsString('FIRST-1', $text);
        $this->assertStringContainsString('SECOND-1', $text);
        $this->assertLessThan(strpos($text, 'SECOND-1'), strpos($text, 'FIRST-1'));
    }

    public function testCreateUsesTheGivenMerger(): void
    {
        $merger = new Fpdf((new Config())->setPdfTitle('MERGER-UNDER-TEST'));

        $pdfMerge = PdfMerge::create($merger);
        $pdfMerge->mergePdfString($this->createPdfString('SOURCE'));

        $this->assertStringContainsString('MERGER-UNDER-TEST', $pdfMerge->toPdf());
    }

    public function testCreateFallsBackToTheDefaultMerger(): void
    {
        $pdfMerge = PdfMerge::create();
        $pdfMerge->mergePdfString($this->createPdfString('SOURCE'));

        $this->assertEquals(1, PdfContents::pageCount($pdfMerge->toPdf()));
    }

    /** Fpdf makes a5 half a millimetre wider than ISO 216, see {@see \Vianetz\Pdf\Model\PaperSize}. */
    public function testMergedDocumentUsesTheConfiguredPaperSize(): void
    {
        $pdfMerge = PdfMerge::create(new Fpdf((new Config())->setPdfSize('a5')));
        $pdfMerge->mergePdfString($this->createPdfString('SOURCE'));

        $this->assertMatchesRegularExpression('/MediaBox\s*\[\s*0\s+0\s+420\.\d+\s+595\.\d+\s*\]/', $pdfMerge->toPdf());
    }
}
