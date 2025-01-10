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

class PdfDocument implements Pdfable
{
    protected string $pdfFile;

    public function __construct(string $pdfFile)
    {
        $this->pdfFile = $pdfFile;
    }

    public function toPdf(): string
    {
        $fileContents = \file_get_contents($this->pdfFile);
        if ($fileContents === false) {
            return '';
        }

        return $fileContents;
    }
}