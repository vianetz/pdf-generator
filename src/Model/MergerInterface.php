<?php
/**
 * Pdf merger interface class
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

namespace Vianetz\Pdf\Model;

interface MergerInterface
{
    /**
     * Merge the specified PDF string into the current file.
     *
     * @param string $pdfString
     * @param null|string $pdfBackgroundFile
     * @param null|string $pdfBackgroundFileForFirstPage
     *
     * @return void
     */
    public function mergePdfFile($pdfString, $pdfBackgroundFile = null, $pdfBackgroundFileForFirstPage = null);

    /**
     * Return the merged PDF contents as string.
     *
     * @return string
     */
    public function getPdfContents();

    /**
     * @param string $fileName
     * @param integer $pageNumber
     */
    public function importPageFromFile($fileName, $pageNumber);

    /**
     * @param string $pdfString
     * @param integer $pageNumber
     */
    public function importPageFromPdfString($pdfString, $pageNumber);

    /**
     * @param string $fileName
     *
     * @return integer
     */
    public function countPages($fileName);

    /**
     * @return \Vianetz\Pdf\Model\MergerInterface
     */
    public function addPage();
}
