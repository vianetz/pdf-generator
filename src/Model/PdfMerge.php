<?php
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

namespace Vianetz\Pdf\Model;

use Vianetz\Pdf\Model\Merger\Fpdi;
use Vianetz\Pdf\NoDataException;

class PdfMerge implements PdfInterface
{
    /** @var \Vianetz\Pdf\Model\MergerInterface */
    private $merger;

    /** @var int */
    private $pageCount = 0;

    public function __construct(MergerInterface $merger = null)
    {
        if ($merger === null) {
            $merger = new Fpdi();
        }

        $this->merger = $merger;
    }

    /**
     * @return \Vianetz\Pdf\Model\PdfMerge
     */
    public static function create(MergerInterface $merger = null)
    {
        return new self($merger);
    }

    /**
     * @deprecated
     * @return \Vianetz\Pdf\Model\PdfMerge
     */
    public static function createWithMerger(MergerInterface $merger)
    {
        return self::create($merger);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $pdfFile
     * @param null|string $pdfBackgroundFile
     * @param null|string $pdfBackgroundFileForFirstPage
     *
     * @return void
     */
    public function mergePdfFile($pdfFile, $pdfBackgroundFile = null, $pdfBackgroundFileForFirstPage = null)
    {
        $fileContents = \file_get_contents($pdfFile);
        if ($fileContents === false) {
            return;
        }

        $this->mergePdfString($fileContents, $pdfBackgroundFile, $pdfBackgroundFileForFirstPage);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $pdfString
     * @param string|null $pdfBackgroundFile
     * @param string|null $pdfBackgroundFileForFirstPage
     *
     * @return void
     */
    public function mergePdfString($pdfString, $pdfBackgroundFile = null, $pdfBackgroundFileForFirstPage = null)
    {
        $pageCount = $this->merger->countPages($pdfString);
        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $this->merger->addPage();
            $this->pageCount++;

            if ($pageNumber === 1 && ! empty($pdfBackgroundFileForFirstPage)) {
                $this->merger->importBackgroundTemplateFile($pdfBackgroundFileForFirstPage);
            } elseif ($pageNumber !== 1 && ! empty($pdfBackgroundFile)) {
                $this->merger->importBackgroundTemplateFile($pdfBackgroundFile);
            }

            $this->merger->importPageFromPdfString($pdfString, $pageNumber);
        }
    }

    /**
     * @deprecated use self::getContents() instead
     *
     * @return string
     * @throws \Vianetz\Pdf\NoDataException
     */
    public function getResult()
    {
        return $this->getContents();
    }

    /**
     * {@inheritDoc}
     */
    final public function getContents()
    {
        if ($this->pageCount === 0) {
            throw new NoDataException('No data to print.');
        }

        return $this->merger->getPdfContents();
    }

    /**
     * {@inheritDoc}
     */
    final public function saveToFile($fileName)
    {
        $pdfContents = $this->getContents();

        return @file_put_contents($fileName, $pdfContents) !== false;
    }
}