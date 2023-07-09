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

use Vianetz\Pdf\NoDataException;

class Pdf implements PdfInterface
{
    /**
     * The generator instance.
     *
     * @var GeneratorInterface
     */
    private $generator;

    /** @var \Vianetz\Pdf\Model\PdfMerge */
    private $pdfMerge;

    /**
     * The (cached) pdf contents.
     *
     * @var string|null
     */
    private $contents;

    /**
     * Initialize empty array for PDF documents to print.
     *
     * @var array<\Vianetz\Pdf\Model\DocumentInterface|\Vianetz\Pdf\Model\PdfDocumentInterface>
     */
    private $documents = [];

    /** @var Config */
    protected $config;

    /** @var \Vianetz\Pdf\Model\EventManagerInterface */
    protected $eventManager;

    /**
     * Default constructor initializes pdf generator.
     *
     * A custom generator class may be injected via $this->setGenerator(), otherwise the default DomPdf generator is used.
     */
    final public function __construct(
        Config $config,
        EventManagerInterface $eventManager,
        GeneratorInterface $generator,
        MergerInterface $merger
    ) {
        $this->generator = $generator;
        $this->pdfMerge = PdfMerge::create($merger);
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritDoc}
     */
    final public function getContents()
    {
        if ($this->contents === null) {
            $this->renderPdfContentsForAllDocuments();
            $this->contents = $this->pdfMerge->getContents();
        }

        return $this->contents;
    }

    /**
     * {@inheritDoc}
     */
    final public function saveToFile($fileName)
    {
        $pdfContents = $this->getContents();

        return @file_put_contents($fileName, $pdfContents) !== false;
    }

    /**
     * Add a new document to generate.
     *
     * @param \Vianetz\Pdf\Model\DocumentInterface|\Vianetz\Pdf\Model\PdfDocumentInterface $documentModel
     *
     * @api
     */
    final public function addDocument($documentModel): self
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
     * @return void
     * @throws \Vianetz\Pdf\NoDataException
     */
    private function renderPdfContentsForAllDocuments()
    {
        $hasData = false;
        foreach ($this->documents as $documentInstance) {
            $this->eventManager->dispatch('vianetz_pdf_document_render_before', ['document' => $documentInstance]);

            if ($documentInstance instanceof DocumentInterface) {
                $this->eventManager->dispatch('vianetz_pdf_' . $documentInstance->getDocumentType() . '_document_render_before', ['document' => $documentInstance]);

                $pdfContents = $this->generator->renderPdfDocument($documentInstance);
                if (empty($pdfContents)) {
                    continue;
                }

                $hasData = true;

                $this->eventManager->dispatch('vianetz_pdf_' . $documentInstance->getDocumentType() . '_document_render_after', ['document' => $documentInstance]);

                $this->pdfMerge->mergePdfString($pdfContents, $documentInstance->getPdfBackgroundFile(), $documentInstance->getPdfBackgroundFileForFirstPage());

                $this->eventManager->dispatch('vianetz_pdf_' . $documentInstance->getDocumentType() . '_document_merge_after', [
                    'merger' => $this->pdfMerge,
                    'document' => $documentInstance,
                ]);
            } elseif ($documentInstance instanceof PdfDocumentInterface) {
                $this->pdfMerge->mergePdfFile($documentInstance->getPdfFile());
            }

            $this->eventManager->dispatch('vianetz_pdf_document_render_after', ['document' => $documentInstance]);
        }

        if (! $hasData) {
            throw new NoDataException('No data to print.');
        }
    }
}
