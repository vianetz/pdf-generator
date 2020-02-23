<?php
/**
 * Vianetz Public Pdf Model
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

use Vianetz\Pdf\Exception;
use Vianetz\Pdf\NoDataException;

class Pdf
{
    /**
     * The generator instance.
     *
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @var MergerInterface
     */
    private $merger;

    /**
     * The (cached) pdf contents.
     *
     * @var string|null
     */
    private $contents;

    /**
     * Initialize empty array for PDF documents to print.
     *
     * @var array<\Vianetz\Pdf\Model\DocumentInterface>
     */
    private $documents = array();

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Vianetz\Pdf\Model\EventManagerInterface
     */
    protected $eventManager;

    /**
     * Default constructor initializes pdf generator.
     *
     * A custom generator class may be injected via $this->setGenerator(), otherwise the default DomPdf generator is used.
     *
     * @param \Vianetz\Pdf\Model\Config $config
     * @param \Vianetz\Pdf\Model\EventManagerInterface $eventManager
     * @param \Vianetz\Pdf\Model\GeneratorInterface $generator
     * @param \Vianetz\Pdf\Model\MergerInterface $merger
     */
    final public function __construct(
        Config $config,
        EventManagerInterface $eventManager,
        GeneratorInterface $generator,
        MergerInterface $merger
    ) {
        $this->generator = $generator;
        $this->merger = $merger;
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    /**
     * Get pdf file contents as string.
     *
     * @api
     * @return string
     */
    final public function getContents()
    {
        if ($this->contents === null) {
            $this->contents = $this->renderPdfContentsForAllDocuments();
        }

        return $this->contents;
    }

    /**
     * Save pdf contents to file.
     *
     * @param string $fileName
     *
     * @api
     * @return boolean true in case of success
     */
    final public function saveToFile($fileName)
    {
        $pdfContents = $this->getContents();

        return (@file_put_contents($fileName, $pdfContents) !== false);
    }

    /**
     * Add a new document to generate.
     *
     * @param DocumentInterface $documentModel
     *
     * @api
     * @return Pdf
     */
    final public function addDocument(DocumentInterface $documentModel)
    {
        $this->documents[] = $documentModel;
        // Reset cached pdf contents.
        $this->contents = null;

        return $this;
    }

    /**
     * Returns the number of documents added to the generator.
     *
     * @return int
     */
    final public function countDocuments()
    {
        return count($this->documents);
    }

    /**
     * Render method for compatibility reasons.
     *
     * Note:
     * This method only exists for compatibility reasons to provide the same interface as the original Zend_Pdf
     * components.
     *
     * @return string
     */
    final public function render()
    {
        return $this->getContents();
    }

    /**
     * @return \Vianetz\Pdf\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Return merged pdf contents of all documents and save it to single temporary files.
     *
     * @return string
     * @throws NoDataException
     */
    private function renderPdfContentsForAllDocuments()
    {
        $tmpFileNameArray = [];
        foreach ($this->documents as $documentInstance) {
            if (! $documentInstance instanceof DocumentInterface) {
                continue;
            }

            $this->eventManager->dispatch('vianetz_pdf_document_render_before', ['document' => $documentInstance]);
            $this->eventManager->dispatch('vianetz_pdf_' . $documentInstance->getDocumentType() . '_document_render_before', ['document' => $documentInstance]);

            $pdfContents = $this->generator->renderPdfDocument($documentInstance);

            $this->eventManager->dispatch('vianetz_pdf_document_render_after', ['document' => $documentInstance]);
            $this->eventManager->dispatch('vianetz_pdf_' . $documentInstance->getDocumentType() . '_document_render_after', ['document' => $documentInstance]);

            $tmpFileName = $this->getTmpFilename();
            @file_put_contents($tmpFileName, $pdfContents);
            $tmpFileNameArray[] = $tmpFileName;

            $this->merger->mergePdfFile($tmpFileName, $documentInstance->getPdfBackgroundFile(), $documentInstance->getPdfBackgroundFileForFirstPage());
            $this->merger->mergePdfFile($documentInstance->getPdfAttachmentFile());

            $this->eventManager->dispatch('vianetz_pdf_' . $documentInstance->getDocumentType() . '_document_merge_after', ['merger' => $this->merger, 'document' => $documentInstance]);
        }

        if (count($tmpFileNameArray) === 0) {
            throw new NoDataException('No data to print.');
        }

        $this->deleteTmpFiles($tmpFileNameArray);

        return $this->merger->getPdfContents();
    }

    /**
     * Return temporary filename for merging.
     *
     * @return string
     * @throws Exception
     */
    private function getTmpFilename()
    {
        if (is_writable($this->config->getTempDir()) === false) {
            throw new Exception('TempDir ' . $this->config->getTempDir() . ' is not writable.');
        }

        return $this->config->getTempDir() . DIRECTORY_SEPARATOR . uniqid((string)time()) . '.pdf';
    }

    /**
     * Remove the temporary files specified as parameter.
     *
     * @param array<string> $fileNames
     *
     * @return $this
     */
    private function deleteTmpFiles(array $fileNames)
    {
        foreach ($fileNames as $fileName) {
            @unlink($fileName);
        }

        return $this;
    }
}
