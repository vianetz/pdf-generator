<?php
/**
 * Zend_Pdf generator class
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

/**
 * Class Vianetz_Pdf_Model_Merger_Zendpdf
 *
 * @deprecated
 */
final class Zendpdf extends AbstractMerger
{
    /**
     * Merge all specified PDF files into one and return that contents.
     *
     * @param array $fileNames The file names to merge.
     *
     * @return string The merged PDF string content.
     */
    public function mergePdfFiles(array $fileNames)
    {
        $resultPdf = new \Zend_Pdf();

        foreach ($fileNames as $fileName) {
            $pdf = \Zend_Pdf::load($fileName);
            $extractor = new \Zend_Pdf_Resource_Extractor();
            foreach ($pdf->pages as $page) {
                $pdfExtract = $extractor->clonePage($page);
                $resultPdf->pages[] = $pdfExtract;
            }
        }

        return $resultPdf->render();
    }
}
