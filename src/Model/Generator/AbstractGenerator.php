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

use Vianetz\Pdf\Model\Config;
use Vianetz\Pdf\Model\Pdfable;

/**
 * A note on PDF merging:
 * - each generator instance generates the PDF of one source
 * - the merge() method in this class takes care of the merging of the single files
 * This is necessary because the documents may contain source dependent information in header/footer like invoice id,
 * addresses, etc. that cannot be determined correctly if you put all in one file.
 *
 * A note on naming convention:
 * - "document" is the PDF contents of a source that serves as input for the generator (e.g. html string)
 */
abstract class AbstractGenerator implements Pdfable // @todo rename Generator to Converter
{
    public const DEBUG_FILE_NAME = 'vianetz_pdf_generator_debug.html';
    protected Config $config;

    abstract public function import(string $html): self;

    public function __construct(?Config $config = null)
    {
        $this->config = $config ?? new Config();
    }

    protected function writeDebugFile(string $fileContents): bool
    {
        if (! $this->config->isDebugMode()) {
            return false;
        }

        $debugFilename = $this->config->getTempDir() . DIRECTORY_SEPARATOR . self::DEBUG_FILE_NAME;

        return @file_put_contents($debugFilename, $fileContents, FILE_APPEND) !== false;
    }
}
