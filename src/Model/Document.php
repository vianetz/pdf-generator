<?php
/**
 * Pdf document class
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

class Document implements DocumentInterface
{
    /** @var string */
    private $htmlContents;

    /** @var string */
    private $pdfBackgroundFile = '';

    /** @var string */
    private $pdfBackgroundFileForFirstPage = '';

    /**
     * @param string $htmlContents
     *
     * @return \Vianetz\Pdf\Model\Document
     */
    public function setHtmlContents($htmlContents)
    {
        $this->htmlContents = $htmlContents;
        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlContents()
    {
        return $this->htmlContents;
    }

    /**
     * @return string
     */
    public function getPdfBackgroundFile()
    {
        return $this->pdfBackgroundFile;
    }

    /**
     * @param string $pdfFile
     * @return void
     */
    public function setPdfBackgroundFile($pdfFile)
    {
        $this->pdfBackgroundFile = $pdfFile;
    }

    /**
     * @return string
     */
    public function getPdfBackgroundFileForFirstPage()
    {
        return $this->pdfBackgroundFileForFirstPage;
    }

    /**
     * @param string $pdfFile
     * @return void
     */
    public function setPdfBackgroundFileForFirstPage($pdfFile)
    {
        $this->pdfBackgroundFileForFirstPage = $pdfFile;
    }

    /**
     * @return string
     */
    public function getDocumentType()
    {
        return '';
    }
}
