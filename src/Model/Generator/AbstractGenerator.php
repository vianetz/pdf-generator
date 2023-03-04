<?php
/**
 * Pdf generator abstract class
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

namespace Vianetz\Pdf\Model\Generator;

use Vianetz\Pdf\Model\GeneratorInterface;

/**
 * Class Vianetz_Pdf_Model_Abstract
 *
 * A note on PDF merging:
 * - each generator instance generates the PDF of one source
 * - the merge() method in this class takes care of the merging of the single files
 * This is necessary because the documents may contain source dependent information in header/footer like invoice id,
 * addresses, etc. that cannot be determined correctly if you put all in one file.
 *
 * A note on naming convention:
 * - "document" means the PDF representation of a source that serves as input for the generator
 */
abstract class AbstractGenerator implements GeneratorInterface
{
    /** @var string */
    const DEBUG_FILE_NAME = 'vianetz_pdf_generator_debug.html';

    /** @var \Vianetz\Pdf\Model\Config $config */
    protected $config;

    /**
     * Constructor initializes configuration values.
     *
     * @param \Vianetz\Pdf\Model\Config|null $config
     */
    public function __construct(\Vianetz\Pdf\Model\Config $config = null)
    {
        if (empty($config) === true) {
            $config = new \Vianetz\Pdf\Model\Config();
        }

        $this->config = $config;

        $this->initGenerator();
    }

    /**
     * Initialize the generator instance.
     *
     * @return \Vianetz\Pdf\Model\Generator\AbstractGenerator
     */
    protected function initGenerator()
    {
        // By default we do nothing..
        return $this;
    }

    /**
     * Replace special characters for DomPDF library.
     *
     * @param string $htmlContents
     *
     * @return string
     */
    protected function replaceSpecialChars($htmlContents)
    {
        // Nothing to do at the moment.

        return $htmlContents;
    }

    protected function writeDebugFile(string $fileContents): bool
    {
        if ($this->config->isDebugMode() === false) {
            return false;
        }

        $debugFilename = $this->config->getTempDir() . DIRECTORY_SEPARATOR . self::DEBUG_FILE_NAME;

        return @file_put_contents($debugFilename, $fileContents, FILE_APPEND) !== false;
    }
}
