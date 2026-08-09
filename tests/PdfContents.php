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

namespace Vianetz\Pdf\Test;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

/** Assertion helpers that look into generated pdf contents instead of just checking they are not empty. */
final class PdfContents
{
    public static function pageCount(string $pdfContents): int
    {
        return (new Fpdi())->setSourceFile(StreamReader::createByString($pdfContents));
    }

    /**
     * The inflated content streams, i.e. the text as it is drawn - depending on the font either plain or
     * utf-16, see {@see self::nullBytePadded()}. Not extracted text.
     */
    public static function text(string $pdfContents): string
    {
        $streamCount = preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdfContents, $matches);
        if ($streamCount === false) {
            // Returning '' here would surface as a baffling assertion failure further down.
            throw new \RuntimeException(sprintf('could not read the pdf content streams, preg error %d', preg_last_error()));
        }

        if ($streamCount === 0) {
            return '';
        }

        $streams = '';
        foreach ($matches[1] as $stream) {
            $inflatedStream = @gzuncompress($stream);
            $streams .= $inflatedStream !== false ? $inflatedStream : $stream;
        }

        return $streams;
    }

    /** The utf-16 representation, i.e. how fonts like DejaVu Sans draw the given text. */
    public static function nullBytePadded(string $text): string
    {
        $paddedText = '';
        foreach (str_split($text) as $character) {
            $paddedText .= "\0" . $character;
        }

        return $paddedText;
    }
}
