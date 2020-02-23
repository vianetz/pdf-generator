<?php
/**
 * Pdf document interface class
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

/**
 * Interface DocumentInterface
 */
interface DocumentInterface
{
    /**
     * Return HTML file contents for conversion to PDF.
     *
     * @return string
     */
    public function getHtmlContents();

    /**
     * @return string
     */
    public function getPdfBackgroundFile();

    /**
     * @param string $pdfFile
     * @return void
     */
    public function setPdfBackgroundFile($pdfFile);

    /**
     * @return string
     */
    public function getPdfBackgroundFileForFirstPage();

    /**
     * @param string $pdfFile
     * @return void
     */
    public function setPdfBackgroundFileForFirstPage($pdfFile);

    /**
     * @return string
     */
    public function getPdfAttachmentFile();

    /**
     * @param string $pdfFile
     * @return void
     */
    public function setPdfAttachmentFile($pdfFile);

    /**
     * @return string
     */
    public function getDocumentType();
}
