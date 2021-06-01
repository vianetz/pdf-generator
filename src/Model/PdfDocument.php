<?php

namespace Vianetz\Pdf\Model;

final class PdfDocument implements PdfDocumentInterface
{
    /** @var string */
    private $pdfFile;

    public function getPdfFile()
    {
        return $this->pdfFile;
    }

    public function setPdfFile($pdfFile)
    {
        $this->pdfFile = $pdfFile;
    }
}