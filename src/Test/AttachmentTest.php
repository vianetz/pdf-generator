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
use Vianetz\Pdf\Model\Merger\Fpdf;
use Vianetz\Pdf\Model\Merger\Fpdi;
use Vianetz\Pdf\Model\Merger\ZugferdFpdf;
use Vianetz\Pdf\Model\NoneEventManager;
use Vianetz\Pdf\Model\Pdf;

/**
 * Attaching files to the generated pdf, i.e. the ZUGFeRD / Factur-X use case. Only ZugferdFpdf can do this,
 * so most tests here are skipped without the suggested horstoeko/zugferd package.
 */
final class AttachmentTest extends TestCase
{
    private const XML_CONTENTS = '<?xml version="1.0" encoding="UTF-8"?><CrossIndustryInvoice/>';

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

    private function requireZugferdPackage(): void
    {
        if (! class_exists(\horstoeko\zugferd\ZugferdPdfWriter::class)) {
            self::markTestSkipped('the suggested package horstoeko/zugferd is not installed');
        }
    }

    private function createXmlFile(): string
    {
        // tempnam() creates the file it returns, so that one needs cleaning up as well.
        $tempFileName = (string) tempnam(sys_get_temp_dir(), 'vianetz-pdf-test-attachment');
        $fileName = $tempFileName . '.xml';

        file_put_contents($fileName, self::XML_CONTENTS);
        $this->tmpFiles[] = $tempFileName;
        $this->tmpFiles[] = $fileName;

        return $fileName;
    }

    private function createPdf(string $marker = 'INVOICE-MARKER'): Pdf
    {
        $config = new Config();

        $pdf = new Pdf($config, new NoneEventManager(), new Dompdf($config), new ZugferdFpdf($config));
        $pdf->add(new HtmlDocument('<html><body>' . $marker . '</body></html>'));

        return $pdf;
    }

    public function testDefaultMergerCannotAddAttachments(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote(Fpdf::class, '/') . '/');

        (new Fpdf())->addAttachment('attachment.xml');
    }

    private function requireTcpdfPackage(): void
    {
        if (! class_exists(\TCPDF::class)) {
            self::markTestSkipped('the suggested package tecnickcom/tcpdf is not installed');
        }
    }

    public function testTcpdfMergerCannotAddAttachments(): void
    {
        $this->requireTcpdfPackage();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote(Fpdi::class, '/') . '/');

        (new Fpdi())->addAttachment('attachment.xml');
    }

    public function testZugferdMergerEmbedsTheAttachment(): void
    {
        $this->requireZugferdPackage();

        $xmlFile = $this->createXmlFile();

        $pdf = $this->createPdf();
        $pdf->attach($xmlFile);

        $pdfContents = $pdf->toPdf();

        $this->assertEquals(1, PdfContents::pageCount($pdfContents));
        $this->assertStringContainsString('EmbeddedFile', $pdfContents);
        $this->assertStringContainsString(basename($xmlFile), $pdfContents);
    }

    public function testDocumentWithoutAttachmentsIsRenderedAsUsual(): void
    {
        $this->requireZugferdPackage();

        $pdfContents = $this->createPdf()->toPdf();

        $this->assertEquals(1, PdfContents::pageCount($pdfContents));
        $this->assertStringContainsString('INVOICE-MARKER', PdfContents::text($pdfContents));
    }

    /** The 6.0.0 regression against a real merger instead of a mock - this used to fail, not just drop the attachment. */
    public function testAttachmentAddedAfterAFirstRenderEndsUpInTheSecondRender(): void
    {
        $this->requireZugferdPackage();

        $xmlFile = $this->createXmlFile();

        $pdf = $this->createPdf();
        $firstRender = $pdf->toPdf();
        $this->assertStringNotContainsString(basename($xmlFile), $firstRender);

        $pdf->attach($xmlFile);
        $secondRender = $pdf->toPdf();

        $this->assertEquals(1, PdfContents::pageCount($secondRender));
        $this->assertStringContainsString(basename($xmlFile), $secondRender);
    }

    public function testAttachmentIsNotEmbeddedTwiceWhenRenderingAgain(): void
    {
        $this->requireZugferdPackage();

        $xmlFile = $this->createXmlFile();

        $pdf = $this->createPdf();
        $pdf->attach($xmlFile);
        $pdf->toPdf();

        $pdf->add(new HtmlDocument('<html><body>SECOND-DOCUMENT</body></html>'));
        $secondRender = $pdf->toPdf();

        // How often the writer names an attachment is its own business, so compare instead of hard coding a count.
        $renderedOnce = $this->createPdf();
        $renderedOnce->attach($xmlFile);
        $renderedOnce->add(new HtmlDocument('<html><body>SECOND-DOCUMENT</body></html>'));

        $this->assertEquals(2, PdfContents::pageCount($secondRender));
        $this->assertEquals(
            substr_count($renderedOnce->toPdf(), basename($xmlFile)),
            substr_count($secondRender, basename($xmlFile))
        );
    }
}
