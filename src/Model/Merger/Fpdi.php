<?php
/**
 * FPDI Merger class
 *
 * This class is responsible for merging individual PDF documents and eventually adding the background PDF template file.
 *
 * @section LICENSE
 * This file is created by vianetz <info@vianetz.com>.
 * The code is distributed under the GPL license.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@vianetz.com so we can send you a copy immediately.
 *
 * @category    Vianetz
 * @package     Vianetz\Pdf
 * @author      Christoph Massmann, <C.Massmann@vianetz.com>
 * @link        http://www.vianetz.com
 * @copyright   Copyright (c) since 2006 vianetz - Dipl.-Ing. C. Massmann (http://www.vianetz.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE
 */

namespace Vianetz\Pdf\Model\Merger;

/**
 * Class Vianetz_Pdf_Model_Merger_Fpdi
 */
final class Fpdi extends AbstractMerger
{
    /**
     * @var string
     */
    const OUTPUT_MODE_STRING = 'S';

    /**
     * @var string
     */
    const OUTPUT_FORMAT_LANDSCAPE = 'L';

    /**
     * @var string
     */
    const OUTPUT_FORMAT_PORTRAIT = 'P';

    /**
     * The FPDI model instance.
     *
     * @var FPDI
     */
    private $fpdiModel;

    /**
     * Default constructor initializes the FPDI library.
     */
    public function __construct()
    {
        $this->fpdiModel = new \FPDI();
    }

    /**
     * Import the specified page number from the given file into the current pdf model.
     *
     * @param string $fileName
     * @param int $pageNumber
     *
     * @return Fpdi
     */
    public function importPageFromFile($fileName, $pageNumber)
    {
        $this->fpdiModel->setSourceFile($fileName);
        $pageId = $this->fpdiModel->importPage($pageNumber);

        $this->fpdiModel->useTemplate($pageId);

        return $this;
    }

    /**
     * @return string
     */
    public function getPdfContents()
    {
        return $this->fpdiModel->Output(self::OUTPUT_MODE_STRING);
    }

    /**
     * @param string $fileName
     *
     * @return integer
     */
    public function countPages($fileName)
    {
        return $this->fpdiModel->setSourceFile($fileName);
    }

    /**
     * @return \Vianetz\Pdf\Model\Merger\Fpdi
     */
    public function addPage()
    {
        $this->fpdiModel->addPage();

        return $this;
    }
}
