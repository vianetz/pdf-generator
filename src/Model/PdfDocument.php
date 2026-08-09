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

use Vianetz\Pdf\FileNotFoundException;

class PdfDocument implements Pdfable
{
    protected string $pdfContents;

    /** @throws \Vianetz\Pdf\FileNotFoundException */
    public function __construct(string $pdfFile)
    {
        $this->pdfContents = $this->readFile($pdfFile);
    }

    public function toPdf(): string
    {
        return $this->pdfContents;
    }

    /** @throws \Vianetz\Pdf\FileNotFoundException */
    private function readFile(string $pdfFile): string
    {
        if (empty($pdfFile) || ! is_file($pdfFile)) {
            throw new FileNotFoundException(sprintf('pdf file "%s" does not exist', $pdfFile));
        }

        $fileContents = @\file_get_contents($pdfFile);
        if ($fileContents === false) {
            throw new FileNotFoundException(sprintf('pdf file "%s" is not readable', $pdfFile));
        }

        return $fileContents;
    }
}