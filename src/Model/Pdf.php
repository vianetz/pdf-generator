<?php
declare(strict_types=1);

/**
 * Vianetz Public Pdf Model
 *
 * This class wraps all the internal processes and is the main class that is intended to be used by developers.
 * Usage:
 * 1) Instantiate (optionally with your custom generator class)
 * 2) addDocument($document)
 * 3) toPdf() or saveToFile()
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
use Vianetz\Pdf\Model\Generator\AbstractGenerator;
use Vianetz\Pdf\NoDataException;

class Pdf implements CanSave, Pdfable
{
    private AbstractGenerator $generator;
    private PdfMerge $pdfMerge;
    private ?string $pdfContents = null;

    /**
     * Initialize empty array for PDF documents to print.
     *
     * @var list<\Vianetz\Pdf\Model\Pdfable|\Vianetz\Pdf\Model\Htmlable>
     */
    private array $documents = [];

    protected Config $config;
    protected EventManagerInterface $eventManager;

    /**
     * Default constructor initializes pdf generator.
     *
     * A custom generator class may be injected via $this->setGenerator(), otherwise the default DomPdf generator is used.
     */
    final public function __construct(
        Config $config,
        EventManagerInterface $eventManager,
        AbstractGenerator $generator,
        MergerInterface $merger
    ) {
        $this->generator = $generator;
        $this->pdfMerge = PdfMerge::create($merger);
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    /** {@inheritDoc} */
    final public function toPdf(): string
    {
        if ($this->pdfContents === null) {
            $this->renderPdfContentsForAllDocuments();
            $this->pdfContents = $this->pdfMerge->toPdf();
        }

        $this->eventManager->dispatch('vianetz_pdf_get_contents', ['contents' => $this->pdfContents]);

        return $this->pdfContents;
    }

    /** {@inheritDoc} */
    final public function saveToFile(string $fileName): bool
    {
        $pdfContents = $this->toPdf();

        return @file_put_contents($fileName, $pdfContents) !== false;
    }

    /**
     * @api
     * @param \Vianetz\Pdf\Model\Pdfable|\Vianetz\Pdf\Model\Htmlable $documentModel
     */
    final public function addDocument($documentModel): self
    {
        $this->documents[] = $documentModel;
        // Reset cached pdf contents.
        $this->pdfContents = null;

        return $this;
    }

    /**
     * Returns the number of documents added to the generator.
     */
    final public function countDocuments(): int
    {
        return count($this->documents);
    }

    /**
     * Render method for compatibility reasons.
     *
     * Note:
     * This method only exists for compatibility reasons to provide the same interface as the original Zend_Pdf
     * components.
     */
    final public function render(): string
    {
        return $this->toPdf();
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Return merged pdf contents of all documents and save it to single temporary files.
     *
     * @throws \Vianetz\Pdf\NoDataException|\Vianetz\Pdf\Exception
     */
    private function renderPdfContentsForAllDocuments(): void
    {
        $hasData = false;
        foreach ($this->documents as $documentInstance) {
            $this->eventManager->dispatch('vianetz_pdf_document_render_before', [
                'document' => $documentInstance,
                'merger' => $this->pdfMerge,
            ]);

            if ($documentInstance instanceof Htmlable) {
                $pdfContents = $this->generator->convert($documentInstance->toHtml())->toPdf();
                if (empty($pdfContents)) {
                    continue;
                }
            } elseif ($documentInstance instanceof Pdfable) {
                $pdfContents = $documentInstance->toPdf();
            } else {
                throw new Exception('invalid document type');
            }

            if ($documentInstance instanceof HasBackgroundPdf) {
                $this->pdfMerge->mergePdfString($pdfContents, $documentInstance->getPdfBackgroundFile(), $documentInstance->getPdfBackgroundFileForFirstPage());
            } else {
                $this->pdfMerge->mergePdfString($pdfContents);
            }

            $hasData = true;

            $this->eventManager->dispatch('vianetz_pdf_document_render_after', [
                'document' => $documentInstance,
                'merger' => $this->pdfMerge,
            ]);
        }

        if (! $hasData) {
            throw new NoDataException('No data to print.');
        }
    }
}
