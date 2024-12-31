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

class Document implements DocumentInterface
{
    private string $htmlContents;
    private string $pdfBackgroundFile = '';
    private string $pdfBackgroundFileForFirstPage = '';

    public function setHtmlContents(string $htmlContents): self
    {
        $this->htmlContents = $htmlContents;

        return $this;
    }

    public function getHtmlContents(): string
    {
        return $this->htmlContents;
    }

    public function getPdfBackgroundFile(): string
    {
        return $this->pdfBackgroundFile;
    }

    public function setPdfBackgroundFile(string $pdfBackgroundFile): void
    {
        $this->pdfBackgroundFile = $pdfBackgroundFile;
    }

    public function getPdfBackgroundFileForFirstPage(): string
    {
        return $this->pdfBackgroundFileForFirstPage;
    }

    public function setPdfBackgroundFileForFirstPage(string $pdfBackgroundFileForFirstPage): void
    {
        $this->pdfBackgroundFileForFirstPage = $pdfBackgroundFileForFirstPage;
    }

    public function getDocumentType(): string
    {
        return '';
    }
}
