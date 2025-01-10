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

namespace Vianetz\Pdf\Model\Merger;

use setasign\Fpdi\Fpdi as FpdfFpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use Vianetz\Pdf\Model\Config;

class Fpdf extends AbstractMerger
{
    private const OUTPUT_MODE_STRING = 'S';
    private const OUTPUT_FORMAT_LANDSCAPE = 'L';
    private const OUTPUT_FORMAT_PORTRAIT = 'P';
    protected FpdfFpdi $fpdiModel;
    private string $orientation = self::OUTPUT_FORMAT_PORTRAIT;
    private string $paper;

    public function __construct(?Config $config = null, ?FpdfFpdi $fpdiModel = null)
    {
        $config ??= new Config();

        if ($config->getPdfOrientation() === Config::PAPER_ORIENTATION_PORTRAIT) {
            $this->orientation = self::OUTPUT_FORMAT_PORTRAIT;
        } elseif ($config->getPdfOrientation() === Config::PAPER_ORIENTATION_LANDSCAPE) {
            $this->orientation = self::OUTPUT_FORMAT_LANDSCAPE;
        }

        $this->paper = $config->getPdfSize();

        $this->fpdiModel = $fpdiModel ?? new FpdfFpdi($this->orientation, 'mm', $this->paper);

        $this->fpdiModel->SetAuthor($config->getPdfAuthor());
        $this->fpdiModel->SetTitle($config->getPdfTitle());
        $this->fpdiModel->setCreator('https://github.com/vianetz/pdf-generator');
    }

    /**
     * Import the specified page number from the given file into the current pdf model.
     *
     * @param string|resource|StreamReader $file
     *
     * @throws \setasign\Fpdi\PdfParser\PdfParserException|\setasign\Fpdi\PdfReader\PdfReaderException
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

    public function toPdf(): string
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

    public function addAttachment(string $fileName): self
    {
        throw new \RuntimeException('not implemented');
    }

    private function createPdfStream(string $pdfString): StreamReader
    {
        return StreamReader::createByString($pdfString);
    }
}
