<?php
/**
 * FPDI Merger class
 *
 * This class is responsible for merging individual PDF documents and eventually adding the background PDF template file.
 *
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

namespace Vianetz\Pdf\Model\Merger;

use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\Tcpdf\Fpdi as FpdiModel;
use Vianetz\Pdf\Model\Config;

final class Fpdi extends AbstractMerger
{
    /** @var string */
    private const OUTPUT_MODE_STRING = 'S';

    /** @var string */
    private const OUTPUT_FORMAT_LANDSCAPE = 'L';

    /** @var string */
    private const OUTPUT_FORMAT_PORTRAIT = 'P';

    private FpdiModel $fpdiModel;
    private string $orientation = self::OUTPUT_FORMAT_PORTRAIT;
    private string $paper = 'a4';

    public function __construct(?\Vianetz\Pdf\Model\Config $config = null)
    {
        $config ??= new \Vianetz\Pdf\Model\Config();

        if ($config->getPdfOrientation() === Config::PAPER_ORIENTATION_PORTRAIT) {
            $this->orientation = self::OUTPUT_FORMAT_PORTRAIT;
        } elseif ($config->getPdfOrientation() === Config::PAPER_ORIENTATION_LANDSCAPE) {
            $this->orientation = self::OUTPUT_FORMAT_LANDSCAPE;
        }

        $this->fpdiModel = new FpdiModel($this->orientation, 'mm', $this->paper);

        $this->fpdiModel->SetAuthor($config->getPdfAuthor());
        $this->fpdiModel->SetTitle($config->getPdfTitle());
        $this->fpdiModel->setCreator('vianetz PDF Generator (https://github.com/vianetz/pdf-generator)');
        $this->fpdiModel->setPrintHeader(false);
        $this->fpdiModel->setPrintFooter(false);

        $this->paper = $config->getPdfSize();
    }

    /**
     * Import the specified page number from the given file into the current pdf model.
     *
     * @param string|resource|StreamReader $file
     *
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function importPageFromFile($file, int $pageNumber): void
    {
        $this->fpdiModel->setSourceFile($file);
        $pageId = $this->fpdiModel->importPage($pageNumber);
        $this->fpdiModel->useTemplate($pageId);
    }

    public function importPageFromPdfString(string $pdfString, int $pageNumber): void
    {
        $this->importPageFromFile($this->createPdfStream($pdfString), $pageNumber);
    }

    public function getPdfContents(): string
    {
        return $this->fpdiModel->Output('', self::OUTPUT_MODE_STRING);
    }

    public function countPages(string $pdfString): int
    {
        return $this->fpdiModel->setSourceFile($this->createPdfStream($pdfString));
    }

    public function addPage(): self
    {
        $this->fpdiModel->addPage($this->orientation, $this->paper);

        return $this;
    }

    private function createPdfStream(string $pdfString): \setasign\Fpdi\PdfParser\StreamReader
    {
        return StreamReader::createByString($pdfString);
    }
}
