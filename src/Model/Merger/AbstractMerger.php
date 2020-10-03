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

namespace Vianetz\Pdf\Model\Merger;

use Vianetz\Pdf\Model\MergerInterface;

abstract class AbstractMerger implements MergerInterface
{
    /**
     * Add the background pdf (if enabled and file exists).
     *
     * @param string $pdfBackgroundFile
     *
     * @return \Vianetz\Pdf\Model\Merger\AbstractMerger
     */
    public function importBackgroundTemplateFile($pdfBackgroundFile)
    {
        if (empty($pdfBackgroundFile) || ! file_exists($pdfBackgroundFile)) {
            return $this;
        }

        $this->importPageFromFile($pdfBackgroundFile, 1); // We assume the background pdf has only one page.

        return $this;
    }
}
