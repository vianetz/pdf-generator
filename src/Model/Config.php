<?php
declare(strict_types=1);

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
 * @package     Vianetz\Pdf
 * @author      Christoph Massmann, <cm@vianetz.com>
 * @link        https://www.vianetz.com
 * @copyright   Copyright (c) since 2006 vianetz - Dipl.-Ing. C. Massmann (https://www.vianetz.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE
 */

namespace Vianetz\Pdf\Model;

final class Config
{
    public const PAPER_ORIENTATION_LANDSCAPE = 'landscape';
    public const PAPER_ORIENTATION_PORTRAIT = 'portrait';

    /** @see \Dompdf\Adapter\CPDF::$PAPER_SIZES */
    private string $pdfSize = 'a4';
    private string $pdfOrientation = self::PAPER_ORIENTATION_PORTRAIT;
    private string $pdfAuthor = '';
    private string $pdfTitle = '';
    private bool $isDebugMode = false;
    private string $tempDir;
    private string $chrootDir = '/';

    public function __construct()
    {
        $this->tempDir = sys_get_temp_dir();
    }

    public function getPdfSize(): string
    {
        return $this->pdfSize;
    }

    public function getPdfOrientation(): string
    {
        return $this->pdfOrientation;
    }

    public function getPdfAuthor(): string
    {
        return $this->pdfAuthor;
    }

    public function getPdfTitle(): string
    {
        return $this->pdfTitle;
    }

    public function isDebugMode(): bool
    {
        return $this->isDebugMode;
    }

    public function getTempDir(): string
    {
        return $this->tempDir;
    }

    public function getChrootDir(): string
    {
        return $this->chrootDir;
    }

    public function setPdfSize(string $pdfSize): self
    {
        $this->pdfSize = $pdfSize;

        return $this;
    }

    public function setPdfOrientation(string $pdfOrientation): self
    {
        $this->pdfOrientation = $pdfOrientation;

        return $this;
    }

    public function setPdfAuthor(string $pdfAuthor): self
    {
        $this->pdfAuthor = $pdfAuthor;

        return $this;
    }

    public function setPdfTitle(string $pdfTitle): self
    {
        $this->pdfTitle = $pdfTitle;

        return $this;
    }

    public function setIsDebugMode(bool $isDebugMode): self
    {
        $this->isDebugMode = $isDebugMode;

        return $this;
    }

    public function setTempDir(string $tempDir): self
    {
        $this->tempDir = $tempDir;

        return $this;
    }

    public function setChrootDir(string $chrootDir): self
    {
        $this->chrootDir = $chrootDir;

        return $this;
    }
}