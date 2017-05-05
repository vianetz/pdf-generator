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
 * @category    Vianetz
 * @package     Vianetz/Pdf
 * @author      Christoph Massmann, <C.Massmann@vianetz.com>
 * @link        http://www.vianetz.com
 * @copyright   Copyright (c) since 2006 vianetz - Dipl.-Ing. C. Massmann (http://www.vianetz.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE
 */

namespace Vianetz\Pdf\Model\Merger;

use Vianetz\Pdf\Model\MergerInterface;

/**
 * Abstract merger class
 */
abstract class AbstractMerger implements MergerInterface
{
    /**
     * Merge the specified PDF file into the current file.
     *
     * @param string $fileName
     * @param null|string $pdfBackgroundFile
     * @param null|string $pdfBackgroundFileForFirstPage
     *
     * @return \Vianetz\Pdf\Model\Merger\AbstractMerger
     */
    public function mergePdfFile($fileName, $pdfBackgroundFile = null, $pdfBackgroundFileForFirstPage = null)
    {
        if (empty($fileName) === true || file_exists($fileName) === false) {
            return $this;
        }

        for ($pageNumber = 1; $pageNumber <= $this->countPages($fileName); $pageNumber++) {
            $this->addPage();

            if ($pageNumber === 1 && empty($pdfBackgroundFileForFirstPage) === false) {
                $this->importBackgroundTemplateFile($pdfBackgroundFileForFirstPage);
            } elseif (empty($pdfBackgroundFile) === false) {
                $this->importBackgroundTemplateFile($pdfBackgroundFile);
            }

            $this->importPageFromFile($fileName, $pageNumber);
        }

        return $this;
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
        if (empty($pdfBackgroundFile) === true || file_exists($pdfBackgroundFile) === false) {
            return $this;
        }

        $this->importPageFromFile($pdfBackgroundFile, 1); // We assume the background pdf has only one page.

        return $this;
    }
}