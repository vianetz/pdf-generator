# vianetz Pdf Library

This library offers an easy-to-use API for PDF generation and merging.  
Internally it uses the [DomPDF library](https://github.com/dompdf/dompdf) for PDF generation and [FPDI](https://github.com/Setasign/FPDI) for merging.

More information about this PDF API can also be found [on my website](https://www.vianetz.com/en/pdf-invoice-api-magento/).

## Installation

```bash
composer require vianetz/pdf-generator
```

Requires PHP 7.4 or later. Optionally install `horstoeko/zugferd` for attachments (ZUGFeRD / Factur-X)
or `tecnickcom/tcpdf` for the TCPDF based merger.

## Usage

### Create PDF document from HTML

```php
// Create a new pdf instance.
$pdf = \Vianetz\Pdf\Model\PdfFactory::general()->create();

// Create the document. You can return any kind of HTML content here.
$document = new \Vianetz\Pdf\Model\HtmlDocument('<strong>Hello</strong> World!');
 
// Add our document to the pdf. You can add as many documents as you like
// as they will all be merged into one PDF file.
$pdf->add($document);

// Save the resulting PDF to file test.pdf - That's it :-)
$pdf->saveToFile('test.pdf');
```

Use `$pdf->toPdf()` to get the raw contents instead. Besides `HtmlDocument` you may also add existing
PDF files via `$pdf->add(new \Vianetz\Pdf\Model\PdfDocument('terms.pdf'))`.

### Configuration

```php
$config = (new \Vianetz\Pdf\Model\Config())
    ->setPdfSize('a4')
    ->setPdfOrientation(\Vianetz\Pdf\Model\Config::PAPER_ORIENTATION_LANDSCAPE)
    ->setPdfAuthor('vianetz')
    ->setPdfTitle('Invoice 1000001')
    ->setIsDebugMode(true);

$pdf = \Vianetz\Pdf\Model\PdfFactory::general()->create($config);
```

Supported paper sizes are `a3`, `a4`, `a5`, `letter` and `legal` - any other size throws an
`UnsupportedPaperSizeException`. See `Config` for further settings (temp dir, chroot dir).

### Background templates

Each page can be stamped onto a template PDF, e.g. your letterhead:

```php
$document = new \Vianetz\Pdf\Model\HtmlDocument(
    '<strong>Hello</strong> World!',
    'background.pdf',           // used for every page
    'background-first-page.pdf' // optional, used for the first page instead
);
```

### Merge two PDF files into one PDF
```php
// Load some random PDF contents
$pdfString = file_get_contents('test1.pdf');

// Setup things
$pdfMerge = \Vianetz\Pdf\Model\PdfMerge::create();

// Do the merge - the second argument puts every page on a background template.
$pdfMerge->mergePdfString($pdfString, 'background.pdf');
$pdfMerge->mergePdfString(file_get_contents('test2.pdf'));

// Save the result PDF to file result.pdf.
file_put_contents('result.pdf', $pdfMerge->toPdf());
```

`PdfMerge::create()` optionally takes the merger to use - `Merger\Fpdf` (default) or `Merger\Fpdi` (TCPDF).

### Attachments (ZUGFeRD / Factur-X)

`ZugferdFpdf` is the only merger supporting attachments, all others throw a `\LogicException`. As the
factory always wires up the default merger you need to compose the pdf yourself:

```php
$config = new \Vianetz\Pdf\Model\Config();

$pdf = new \Vianetz\Pdf\Model\Pdf(
    $config,
    new \Vianetz\Pdf\Model\NoneEventManager(),
    new \Vianetz\Pdf\Model\Generator\Dompdf($config),
    new \Vianetz\Pdf\Model\Merger\ZugferdFpdf($config)
);

$pdf->add(new \Vianetz\Pdf\Model\HtmlDocument('<strong>Invoice</strong> 1000001'));
$pdf->attach('factur-x.xml');
$pdf->saveToFile('invoice.pdf');
```

### Error handling

A missing background template, an unreadable PDF file or an empty document throws rather than producing
a partial PDF. All exceptions implement `\Vianetz\Pdf\Exception`, so one catch block covers them all:

```php
try {
    $pdf->saveToFile('test.pdf');
} catch (\Vianetz\Pdf\Exception $e) {
    // NoDataException, FileNotFoundException, InvalidDocumentException, UnsupportedPaperSizeException
}
```

### Tips & Tricks

- The string literal `__PDF_TPC__` will be replaced with the total page count

## Frequently Asked Questions
Please find the Frequently Asked Questions [on my website](https://www.vianetz.com/en/faq).

## Support
If you have any issues or suggestions with this extension, please do not hesitate to
[contact me](https://www.vianetz.com/en/contacts).

## License
[GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html)  
See also LICENSE file.

This extension uses the DomPDF library. For license information please visit [the DomPdf
repository](https://github.com/dompdf/dompdf).  
This extension uses the FPDI library. For license information please visit [the FPDI
repository](https://github.com/Setasign/FPDI/blob/master/LICENSE.txt).

This library uses Semantic Versioning - please find more information at [semver.org](http://semver.org).
