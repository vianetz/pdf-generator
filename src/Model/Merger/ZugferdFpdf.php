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

namespace Vianetz\Pdf\Model\Merger;

use horstoeko\zugferd\ZugferdPdfWriter;
use Vianetz\Pdf\Model\Config;

final class ZugferdFpdf extends Fpdf
{
    public function __construct(?Config $config = null)
    {
        parent::__construct($config, new ZugferdPdfWriter());
    }

    public function addAttachment(string $fileName): self
    {
        $this->fpdiModel->attach($fileName);

        return $this;
    }
}
