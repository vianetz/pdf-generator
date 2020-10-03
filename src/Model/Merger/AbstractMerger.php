<?php
/**
 * Pdf generator abstract class
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

use Vianetz\Pdf\Model\MergerInterface;

abstract class AbstractMerger implements MergerInterface
{
    /**
     * {@inheritDoc}
     *
     * @param string $pdfString
     * @param null|string $pdfBackgroundFile
     * @param null|string $pdfBackgroundFileForFirstPage
     *
     * @return void
     */
    public function mergePdfFile($pdfString, $pdfBackgroundFile = null, $pdfBackgroundFileForFirstPage = null)
    {
        if (empty($pdfString)) {
            return;
        }

        $pageCount = $this->countPages($pdfString);
        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $this->addPage();

            if ($pageNumber === 1 && ! empty($pdfBackgroundFileForFirstPage)) {
                $this->importBackgroundTemplateFile($pdfBackgroundFileForFirstPage);
            } elseif ($pageNumber !== 1 && ! empty($pdfBackgroundFile)) {
                $this->importBackgroundTemplateFile($pdfBackgroundFile);
            }

            $this->importPageFromPdfString($pdfString, $pageNumber);
        }
    }

    /**
     * Add the background pdf (if enabled and file exists).
     *
     * @param string $pdfBackgroundFile
     *
     * @return \Vianetz\Pdf\Model\Merger\AbstractMerger
     */
    protected function importBackgroundTemplateFile($pdfBackgroundFile)
    {
        if (empty($pdfBackgroundFile) || ! file_exists($pdfBackgroundFile)) {
            return $this;
        }

        $this->importPageFromFile($pdfBackgroundFile, 1); // We assume the background pdf has only one page.

        return $this;
    }
}
