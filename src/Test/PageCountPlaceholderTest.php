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
use Vianetz\Pdf\Model\Generator\Dompdf;
use Vianetz\Pdf\Model\HtmlDocument;
use Vianetz\Pdf\Model\PdfFactory;

/** The `__PDF_TPC__` placeholder, replaced by {@see \Vianetz\Pdf\Model\Generator\Dompdf::injectPageCount()}. */
final class PageCountPlaceholderTest extends TestCase
{
    private const PLACEHOLDER = '__PDF_TPC__';

    private function createHtmlWithPageCountFooter(int $pageCount, string $fontFamily = ''): string
    {
        $style = $fontFamily !== '' ? ' style="font-family: ' . $fontFamily . ';"' : '';

        $html = '<html><body' . $style . '>';
        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $pageBreak = $pageNumber < $pageCount ? ' style="page-break-after: always;"' : '';
            $html .= '<div' . $pageBreak . '>page ' . $pageNumber . ' of ' . self::PLACEHOLDER . '</div>';
        }

        return $html . '</body></html>';
    }

    private function generate(string $html): string
    {
        return (new Dompdf(new Config()))->import($html)->toPdf();
    }

    public function testPlaceholderIsReplacedByTheTotalPageCount(): void
    {
        $pdfContents = $this->generate($this->createHtmlWithPageCountFooter(3));

        $this->assertEquals(3, PdfContents::pageCount($pdfContents));
        $this->assertStringContainsString('of 3', PdfContents::text($pdfContents));
    }

    public function testPlaceholderIsReplacedInASinglePageDocument(): void
    {
        $pdfContents = $this->generate($this->createHtmlWithPageCountFooter(1));

        $this->assertStringContainsString('of 1', PdfContents::text($pdfContents));
    }

    public function testNoPlaceholderIsLeftInTheGeneratedDocument(): void
    {
        $pdfContents = $this->generate($this->createHtmlWithPageCountFooter(3));

        $this->assertStringNotContainsString(self::PLACEHOLDER, PdfContents::text($pdfContents));
    }

    public function testNoPlaceholderIsLeftInTheMergedDocument(): void
    {
        $pdf = PdfFactory::general()->create();
        $pdf->add(new HtmlDocument($this->createHtmlWithPageCountFooter(2)));

        $pdfContents = $pdf->toPdf();

        $this->assertStringNotContainsString(self::PLACEHOLDER, PdfContents::text($pdfContents));
        $this->assertStringContainsString('of 2', PdfContents::text($pdfContents));
    }

    /** Utf-16 drawing fonts need the placeholder replaced in that encoding too - the 4.0.1 fix. */
    public function testPlaceholderIsReplacedForFontsDrawingNullBytePaddedText(): void
    {
        $pdfContents = $this->generate($this->createHtmlWithPageCountFooter(3, 'DejaVu Sans'));
        $text = PdfContents::text($pdfContents);

        // Guard the premise: the font really does draw null byte padded.
        $this->assertStringContainsString(PdfContents::nullBytePadded('page '), $text);

        $this->assertStringNotContainsString(PdfContents::nullBytePadded(self::PLACEHOLDER), $text);
        $this->assertStringContainsString(PdfContents::nullBytePadded('of 3'), $text);
    }

    public function testDocumentWithoutPlaceholderIsRenderedUnchanged(): void
    {
        $pdfContents = $this->generate('<html><body>NO-PLACEHOLDER-HERE</body></html>');

        $this->assertStringContainsString('NO-PLACEHOLDER-HERE', PdfContents::text($pdfContents));
    }
}
