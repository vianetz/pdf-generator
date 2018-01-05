<?php
/**
 * Vianetz Pdf Options Model
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
 * @package     Vianetz\Pdf
 * @author      Christoph Massmann, <C.Massmann@vianetz.com>
 * @link        http://www.vianetz.com
 * @copyright   Copyright (c) since 2006 vianetz - Dipl.-Ing. C. Massmann (http://www.vianetz.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE
 */

namespace Vianetz\Pdf\Model;

final class Config
{
    /**
     * @var string
     */
    const PAPER_ORIENTATION_LANDSCAPE = 'landscape';

    /**
     * @var string
     */
    const PAPER_ORIENTATION_PORTRAIT = 'portrait';

    /**
     * @var string
     * @see \Dompdf\Adapter\CPDF::$PAPER_SIZES
     */
    private $pdfSize = 'a4';

    /**
     * @var string
     */
    private $pdfOrientation;

    /**
     * @var string
     */
    private $pdfAuthor = '';

    /**
     * @var string
     */
    private $pdfTitle = '';

    /**
     * @var boolean
     */
    private $isDebugMode = false;

    /**
     * @var string
     */
    private $tempDir = '';

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->tempDir = sys_get_temp_dir();
    }

    /**
     * @return int
     */
    public function getPdfSize()
    {
        return $this->pdfSize;
    }

    /**
     * @return string
     */
    public function getPdfOrientation()
    {
        return $this->pdfOrientation;
    }

    /**
     * @return string
     */
    public function getPdfAuthor()
    {
        return $this->pdfAuthor;
    }

    /**
     * @return string
     */
    public function getPdfTitle()
    {
        return $this->pdfTitle;
    }

    /**
     * @return boolean
     */
    public function isDebugMode()
    {
        return $this->isDebugMode;
    }

    /**
     * @return string
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @param int $pdfSize
     * @return Config
     */
    public function setPdfSize($pdfSize)
    {
        $this->pdfSize = $pdfSize;

        return $this;
    }

    /**
     * @param string $pdfOrientation
     * @return Config
     */
    public function setPdfOrientation($pdfOrientation)
    {
        $this->pdfOrientation = $pdfOrientation;

        return $this;
    }

    /**
     * @param string $pdfAuthor
     * @return Config
     */
    public function setPdfAuthor($pdfAuthor)
    {
        $this->pdfAuthor = $pdfAuthor;

        return $this;
    }

    /**
     * @param string $pdfTitle
     * @return Config
     */
    public function setPdfTitle($pdfTitle)
    {
        $this->pdfTitle = $pdfTitle;

        return $this;
    }

    /**
     * @param boolean $isDebugMode
     * @return Config
     */
    public function setIsDebugMode($isDebugMode)
    {
        $this->isDebugMode = $isDebugMode;

        return $this;
    }

    /**
     * @param string $tempDir
     * @return Config
     */
    public function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;

        return $this;
    }
}