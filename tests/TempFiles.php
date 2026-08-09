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

/**
 * Temporary directories for tests, all below the system temp dir and all removed again.
 *
 * Nothing a test writes may end up in the working directory - that is the repository when the suite is
 * run the usual way, and dompdf drops its font cache next to whatever temp dir it is given.
 */
trait TempFiles
{
    /** @var list<string> */
    private array $tempDirs = [];

    protected function tearDownTempFiles(): void
    {
        foreach ($this->tempDirs as $dirName) {
            foreach ((array) glob($dirName . DIRECTORY_SEPARATOR . '*') as $fileName) {
                @unlink((string) $fileName);
            }
            @rmdir($dirName);
        }

        $this->tempDirs = [];
    }

    /** An empty, writable directory of its own - everything created in it is removed in tear down. */
    protected function createTempDir(): string
    {
        $dirName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('vianetz-pdf-test-', true);
        mkdir($dirName);
        $this->tempDirs[] = $dirName;

        return $dirName;
    }

    /** A file in a directory of its own, so it may be given the name the test actually wants. */
    protected function createTempFile(string $contents = '', string $fileName = 'test.pdf'): string
    {
        $filePath = $this->createTempDir() . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($filePath, $contents);

        return $filePath;
    }

    /** A path that can never be written to, for anyone - unlike a directory made unwritable by mode. */
    protected function unwritablePath(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('vianetz-pdf-missing-', true) . DIRECTORY_SEPARATOR . 'nested';
    }
}
