# vianetz Pdf Library

This library offers an easy-to-use API for PDF generation and merging.  
Internally it uses the [DomPDF library](https://github.com/dompdf/dompdf) for PDF generation and [FPDI](https://github.com/Setasign/FPDI) for merging.

More information about this PDF API can also be found [on my website](https://www.vianetz.com/en/pdf-invoice-api-magento/).

## Usage

### Create PDF document from HTML contents

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

### Merge a PDF file and a PDF string into one PDF file
```php
// Load some random PDF contents
$pdfString = file_get_contents('test1.pdf');

// Setup things
$pdfMerge = \Vianetz\Pdf\Model\PdfMerge::create();

// Do the merge.
$pdfMerge->mergePdfString($pdfString, 'background.pdf');
$pdfMerge->mergePdfFile('test2.pdf');

// Save the result PDF to file result.pdf.
file_put_contents($fileName, $pdfMerge->toPdf());
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
