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

namespace Vianetz\Pdf\Model\Generator;

use Vianetz\Pdf\Model\HtmlDocumentInterface;

/**
 * Known limitations of Dompdf:
 * - colspan is not working properly (table is moved to the top of the page)
 * - no CSS background images with relative paths
 * - dompdf doesn’t like all CSS shortcuts. In particular, font: 12pt Helvetica; and background: #999;
 *   didn’t work as well as explicitly setting the font-family and font-size separately, and setting the
 *   background-color.
 */
final class Dompdf extends AbstractGenerator
{
    private const TOTAL_PAGE_COUNT_PLACEHOLDER = '__PDF_TPC__';

    private \Dompdf\Dompdf $domPdf;

    /** @throws \Exception */
    public function renderPdfDocument(HtmlDocumentInterface $documentModel): string
    {
        $this->initPdf();

        $this->domPdf->loadHtml($this->getHtmlContentsForDocument($documentModel));
        $this->domPdf->render();

        $this->injectPageCount();

        return $this->domPdf->output() ?? '';
    }

    protected function initPdf(): self
    {
        $this->domPdf = new \Dompdf\Dompdf($this->getDompdfOptions());

        $this->domPdf->setPaper($this->config->getPdfSize(), $this->config->getPdfOrientation());

        $this->domPdf->addInfo('Creator', $this->config->getPdfAuthor());
        $this->domPdf->addInfo('Title', $this->config->getPdfTitle());

        return $this;
    }

    /**
     * Return HTML contents for one single document that is later merged with the others.
     *
     * @throws \Exception
     */
    protected function getHtmlContentsForDocument(HtmlDocumentInterface $documentModel): string
    {
        $htmlContents = $documentModel->getHtmlContents();

        $htmlContents = $this->replaceSpecialChars($htmlContents);
        $this->writeDebugFile($htmlContents);

        return $htmlContents;
    }

    /**
     * @see \Dompdf\Options
     *
     * @return array<string,mixed>
     */
    private function getDompdfOptions(): array
    {
        return [
            'isPhpEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => $this->config->getChrootDir(),
            'fontCache' => $this->config->getTempDir(),
        ];
    }

    /** @see https://github.com/dompdf/dompdf/issues/1636#issuecomment-490372233 */
    private function injectPageCount(): void
    {
        /** @var \Dompdf\Adapter\CPDF $canvas */
        $canvas = $this->domPdf->getCanvas();

        $search = [self::TOTAL_PAGE_COUNT_PLACEHOLDER, self::insertNullByteBeforeEachCharacter(self::TOTAL_PAGE_COUNT_PLACEHOLDER)];
        $replace = [(string)$canvas->get_page_count(), self::insertNullByteBeforeEachCharacter((string)$canvas->get_page_count())];

        $pdf = $canvas->get_cpdf();

        foreach ($pdf->objects as &$o) {
            if ($o['t'] === 'contents') {
                $o['c'] = str_replace($search, $replace, $o['c']);
            }
        }
    }

    /** @see https://gist.github.com/enumag/f670865b70d11e0b8156b1e92acc3c92 */
    private static function insertNullByteBeforeEachCharacter(string $string): string
    {
        return "\u{0000}" . substr(chunk_split($string, 1, "\u{0000}"), 0, -1);
    }
}
