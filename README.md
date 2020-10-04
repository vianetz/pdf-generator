vianetz Pdf Library
=====================

Description
-----------
This library offers an easy-to-use API for PDF generation and merging.

Requirements
------------
- PHP >= 5.6

Usage
-----
```php
// Create a new pdf instance.
$pdf = Vianetz\Pdf\Model\PdfFactory::general()->create();

// Create the document. You can return any kind of HTML content here.
$document = new \Vianetz\Pdf\Model\Document();
$document->setHtmlContents('<strong>Hello</strong> World!');
 
// Add our document to the pdf. You can add as many documents as you like
// as they will all be merged into one PDF file.
$pdf->addDocument($document);

// Save the resulting PDF to file test.pdf - That's it :-)
$pdf->saveToFile('test.pdf');
```

Frequently Asked Questions
--------------------------
Please find the Frequently Asked Questions [on our website](https://www.vianetz.com/en/faq).

Support
-------
If you have any issues or suggestions with this extension, please do not hesitate to
[contact me](https://www.vianetz.com/en/contacts).

Credits
-------
Of course this extension would not have been possible without the great open source eco-system.
Therewith credits go to:
- [DomPDF](https://github.com/dompdf/dompdf)
- [FPDI](https://github.com/Setasign/FPDI)

Developer
---------
Christoph Massmann  
[www.vianetz.com](https://www.vianetz.com)  
[@vianetz](https://twitter.com/vianetz)

Licence
-------
[GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html)  
See also LICENSE file.

This extension uses the DomPDF library. For license information please visit [the DomPdf
repository](https://github.com/dompdf/dompdf).  
This extension uses the FPDI library. For license information please visit [the FPDI
repository](https://github.com/Setasign/FPDI/blob/master/LICENSE.txt).

Copyright
---------
(c) since 2008 vianetz

This library uses Semantic Versioning - please find more information at [semver.org](http://semver.org).
