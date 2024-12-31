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

namespace Vianetz\Pdf\Model;

use Vianetz\Pdf\Model\Merger\Fpdi;
use Vianetz\Pdf\NoDataException;

class PdfMerge implements PdfInterface
{
    private MergerInterface $merger;
    private int $pageCount = 0;

    public function __construct(?MergerInterface $merger = null)
    {
        if ($merger === null) {
            $merger = new Fpdi();
        }

        $this->merger = $merger;
    }

    public static function create(?MergerInterface $merger = null): self
    {
        return new self($merger);
    }

    /** @deprecated */
    public static function createWithMerger(MergerInterface $merger): self
    {
        return self::create($merger);
    }

    public function mergePdfFile(string $pdfFile, ?string $pdfBackgroundFile = null, ?string $pdfBackgroundFileForFirstPage = null): void
    {
        $fileContents = \file_get_contents($pdfFile);
        if ($fileContents === false) {
            return;
        }

        $this->mergePdfString($fileContents, $pdfBackgroundFile, $pdfBackgroundFileForFirstPage);
    }

    public function mergePdfString(string $pdfString, ?string $pdfBackgroundFile = null, ?string $pdfBackgroundFileForFirstPage = null): void
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
     * @throws \Vianetz\Pdf\NoDataException
     */
    public function getResult(): string
    {
        return $this->getContents();
    }

    /** {@inheritDoc} */
    final public function getContents(): string
    {
        if ($this->pageCount === 0) {
            throw new NoDataException('No data to print.');
        }

        return $this->merger->getPdfContents();
    }

    /** {@inheritDoc} */
    final public function saveToFile(string $fileName): bool
    {
        $pdfContents = $this->getContents();

        return @file_put_contents($fileName, $pdfContents) !== false;
    }
}