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

use PHPUnit\Framework\TestCase;
use Vianetz\Pdf\Model\PaperSize;
use Vianetz\Pdf\UnsupportedPaperSizeException;

final class PaperSizeTest extends TestCase
{
    /** @return array<string, array{0: string}> */
    public static function supportedSizeNameDataProvider(): array
    {
        return [
            'a3' => ['a3'],
            'a4' => ['a4'],
            'a5' => ['a5'],
            'letter' => ['letter'],
            'legal' => ['legal'],
        ];
    }

    /** @return array<string, array{0: string}> */
    public static function unsupportedSizeNameDataProvider(): array
    {
        return [
            'size only the generator knows' => ['b5'],
            'another size only the generator knows' => ['ra2'],
            'unknown name' => ['this-is-not-a-paper-size'],
            'empty name' => [''],
            'whitespace only' => ['   '],
        ];
    }

    /** @dataProvider supportedSizeNameDataProvider */
    public function testSupportedSizeNameIsAccepted(string $name): void
    {
        $this->assertEquals($name, PaperSize::fromName($name)->toString());
    }

    /** @dataProvider unsupportedSizeNameDataProvider */
    public function testUnsupportedSizeNameThrowsException(string $name): void
    {
        $this->expectException(UnsupportedPaperSizeException::class);

        PaperSize::fromName($name);
    }

    public function testExceptionMessageListsTheSupportedSizes(): void
    {
        $this->expectException(UnsupportedPaperSizeException::class);
        $this->expectExceptionMessageMatches('/a3, a4, a5, letter, legal/');

        PaperSize::fromName('b5');
    }

    public function testSizeNameIsNormalizedToLowercase(): void
    {
        $this->assertEquals('a4', PaperSize::fromName('A4')->toString());
    }

    public function testSurroundingWhitespaceIsIgnored(): void
    {
        $this->assertEquals('a4', PaperSize::fromName(' a4 ')->toString());
    }

    public function testNamesReturnsAllSupportedSizes(): void
    {
        $this->assertEquals(['a3', 'a4', 'a5', 'letter', 'legal'], PaperSize::names());
    }
}
