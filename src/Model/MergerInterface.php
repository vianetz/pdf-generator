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
 * @category    Vianetz
 * @package     Vianetz/Pdf
 * @author      Christoph Massmann, <C.Massmann@vianetz.com>
 * @link        http://www.vianetz.com
 * @copyright   Copyright (c) since 2006 vianetz - Dipl.-Ing. C. Massmann (http://www.vianetz.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE
 */

namespace Vianetz\Pdf\Model;

/**
 * Interface Vianetz_Pdf_Model_Merger_Interface
 */
interface MergerInterface
{
    /**
     * Merge all specified PDF files into one and return that contents.
     *
     * @param string $fileName
     * @param null|string $pdfBackgroundFile
     * @param null|string $pdfBackgroundFileForFirstPage
     *
     * @return string The merged PDF string content.
     */
    public function mergePdfFile($fileName, $pdfBackgroundFile = null, $pdfBackgroundFileForFirstPage = null);

    /**
     * Return the merged PDF contents as string.
     *
     * @return string
     */
    public function getPdfContents();

    /**
     * @param string $fileName
     * @param integer $pageNumber
     *
     * @return \Vianetz\Pdf\Model\MergerInterface
     */
    public function importPageFromFile($fileName, $pageNumber);

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
