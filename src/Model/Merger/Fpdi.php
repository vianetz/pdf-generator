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
use Vianetz\Pdf\Model\Config;

final class Fpdi extends AbstractMerger
{
    /**
     * @var string
     */
    const OUTPUT_MODE_STRING = 'S';

    /**
     * @var string
     */
    const OUTPUT_FORMAT_LANDSCAPE = 'L';

    /**
     * @var string
     */
    const OUTPUT_FORMAT_PORTRAIT = 'P';

    /**
     * The FPDI model instance.
     *
     * @var \setasign\Fpdi\Fpdi
     */
    private $fpdiModel;

    /**
     * @var string
     */
    private $orientation = self::OUTPUT_FORMAT_PORTRAIT;

    /**
     * @var string
     */
    private $paper = 'a4';

    public function __construct(\Vianetz\Pdf\Model\Config $config = null)
    {
        $this->fpdiModel = new \setasign\Fpdi\Fpdi();

        if (empty($config)) {
            $config = new \Vianetz\Pdf\Model\Config();
        }

        $this->fpdiModel->SetAuthor($config->getPdfAuthor());
        $this->fpdiModel->SetTitle($config->getPdfTitle());

        if ($config->getPdfOrientation() === Config::PAPER_ORIENTATION_PORTRAIT) {
            $this->orientation = self::OUTPUT_FORMAT_PORTRAIT;
        } elseif ($config->getPdfOrientation() === Config::PAPER_ORIENTATION_LANDSCAPE) {
            $this->orientation = self::OUTPUT_FORMAT_LANDSCAPE;
        }

        $this->paper = $config->getPdfSize();
    }

    /**
     * Import the specified page number from the given file into the current pdf model.
     *
     * @param string|StreamReader $pdfFile
     * @param int $pageNumber
     *
     * @return void
     */
    public function importPageFromFile($pdfFile, $pageNumber)
    {
        $this->fpdiModel->setSourceFile($pdfFile);
        $pageId = $this->fpdiModel->importPage($pageNumber);
        $this->fpdiModel->useTemplate($pageId);
    }

    /**
     * @param string $pdfString
     * @param int $pageNumber
     *
     * @return void
     */
    public function importPageFromPdfString($pdfString, $pageNumber)
    {
        $this->importPageFromFile($this->createPdfStream($pdfString), $pageNumber);
    }

    /**
     * @return string
     */
    public function getPdfContents()
    {
        return $this->fpdiModel->Output(self::OUTPUT_MODE_STRING);
    }

    /**
     * @param string $pdfString
     *
     * @return integer
     */
    public function countPages($pdfString)
    {
        return $this->fpdiModel->setSourceFile($this->createPdfStream($pdfString));
    }

    /**
     * @return \Vianetz\Pdf\Model\Merger\Fpdi
     */
    public function addPage()
    {
        $this->fpdiModel->addPage($this->orientation, $this->paper);

        return $this;
    }

    /**
     * @param string $pdfString
     *
     * @return \setasign\Fpdi\PdfParser\StreamReader
     */
    private function createPdfStream($pdfString)
    {
        return StreamReader::createByString($pdfString);
    }
}
