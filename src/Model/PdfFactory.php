<?php
/**
 * Vianetz Public Pdf Factory
 *
 * This class wraps all the internal processes and is the main class that is intended to be used by developers.
 * Usage:
 * 1) Instantiate (optionally with your custom generator class)
 * 2) addDocument($document)
 * 3) getContents() or saveToFile()
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

use Vianetz\Pdf\Model\Generator\Dompdf;
use Vianetz\Pdf\Model\Merger\Fpdi;

final class PdfFactory
{
    public function create(Config $config = null, EventManagerInterface $eventManager = null): Pdf
    {
        $config ??= new Config();
        $eventManager ??= new NoneEventManager();

        $generator = new Dompdf($config);
        $merger = new Fpdi($config);

        return new Pdf($config, $eventManager, $generator, $merger);
    }

    /** @return static */
    public static function general()
    {
        return new static();
    }
}