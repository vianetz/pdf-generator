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

use Vianetz\Pdf\UnsupportedPaperSizeException;

/**
 * The paper sizes a document may be rendered in.
 *
 * We deliberately support only those sizes that every pdf library we use knows by name, so that generators
 * and mergers can simply be given the name and still end up with the same page.
 *
 * Note that the libraries do not define a5 in exactly the same way - Fpdf makes it half a millimetre wider
 * than ISO 216 and than the generator does. The difference is not worth carrying our own dimensions for.
 */
final class PaperSize
{
    /** @var list<string> */
    private const SIZES = ['a3', 'a4', 'a5', 'letter', 'legal'];

    private string $name;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    /** @throws \Vianetz\Pdf\UnsupportedPaperSizeException */
    public static function fromName(string $name): self
    {
        $normalizedName = \strtolower(\trim($name));

        if (! \in_array($normalizedName, self::SIZES, true)) {
            throw new UnsupportedPaperSizeException(
                sprintf('unsupported paper size "%s", supported are: %s', $name, implode(', ', self::SIZES))
            );
        }

        return new self($normalizedName);
    }

    /** @return list<string> */
    public static function names(): array
    {
        return self::SIZES;
    }

    /** The size name as the pdf libraries expect it. */
    public function toString(): string
    {
        return $this->name;
    }
}
