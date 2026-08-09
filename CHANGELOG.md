# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/)
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]
### Fixed
- Adding a document or an attachment after a first render no longer fails with "FPDF error: The document
  is closed" - every render now merges into an unused copy of the merger instead of the one the previous
  render has already closed. The 6.0.0 fix only reset the cached contents, which made the second render
  replay all documents into the exhausted merger.
### Changed
- Mergers that keep mutable state in object properties have to implement `__clone()` - the built-in mergers
  do, custom implementations of `MergerInterface` need to follow, see the interface documentation
- The merger passed to `Pdf::__construct()` is no longer written to, as every render works on a copy of it -
  code reaching into that merger instance after a render now finds it empty
- Observers of `vianetz_pdf_document_render_before` / `_after` receive a new `PdfMerge` instance per render,
  so one must not be kept beyond the render it belongs to
### Added
- Tests for background templates, attachments, the `__PDF_TPC__` placeholder and pdf merging

## [6.0.0] - 2026-08-09
### Added
- `PaperSize` model and `UnsupportedPaperSizeException`
### Changed
- **Breaking:** Supported paper sizes limited to a3, a4, a5, letter and legal - `Config::setPdfSize()`
  validates and normalizes the given size right away
- **Breaking:** `Vianetz\Pdf\Exception` is an interface now instead of a base class - catching it is
  unaffected and covers `FileNotFoundException` as well now
- Our exceptions extend the SPL exception matching their nature, i.e. `\RuntimeException` or
  `\InvalidArgumentException`
- Mergers that cannot handle attachments throw a `\LogicException`
### Fixed
- Missing or unreadable files result in a `FileNotFoundException` instead of an empty document
- Attachments added after a first render are no longer silently dropped
- An empty `Pdfable` document results in a `NoDataException` instead of a pdf parser error

## [5.0.0] - 2025-01-10
### Added
- Merger for Zugferd PDFs, i.e. PDFs with XML attachments
### Changed
- TCPDF to Fpdf library for merging as default (both libraries supported now)
- Several public interfaces to support type hints and make purpose clearer
- The general pdf background file is now used for the first page as well, unless a dedicated background
  file for the first page is set - this reverses the behaviour introduced in 1.0.3
- A missing pdf background template file now throws a `FileNotFoundException` instead of being silently
  ignored, i.e. rendering no longer continues without the background
### Removed
- Deprecated ZendPdf merger  

## [4.0.2] - 2024-10-13
### Changed
- Dompdf library to version 3.x

## [4.0.1] - 2023-11-10
### Fixed
- Issue with total page count not being replaced for some font families

## [4.0.0] - 2023-07-09
### Changed
- Raised minimum PHP version to 7.4
- Small refactorings for typehints
- Debug file appends content now
### Fixed
- Issue with pdf author not being set correctly

## [3.1.0] - 2022-08-24
### Changed
- Upgraded DomPDF library to version 2
- Upgraded other dependences
- Raised minimum PHP version to 7

## [3.0.0] - 2021-06-01
### Changed
- Refactored logic for pdf attachment files
- Switched from deprecated FPDF library to TCPDF

## [2.2.0] - 2021-02-05
### Added
- Added support for PHP 8
### Changed
- Updated PDF libraries

## [2.1.0] - 2020-12-19
### Added
- Total page count is now available via placeholder `__PDF_TPC__` 

## [2.0.0] - 2020-11-05
### Changed
- Upgraded DomPDF dependency to 0.8.6 and added chroot setting

## [1.4.0] - 2020-10-15
### Changed
- `PdfMerge` class can now automatically instantiate the merger class

## [1.3.0] - 2020-10-04
### Changed
- No temporary files necessary for pdf merging anymore

## [1.2.2] - 2020-10-03
### Changed
- Updated dependency for DomPdf library (because they introduced breaking changes in 0.8.6)

## [1.2.1] - 2019-07-11
### Fixed
- Improved dependency constraints in ```composer.json``` for better compatibility

## [1.2.0] - 2019-03-28
### Added
- Added setter for pdf background templates

## [1.1.1] - 2019-02-23
### Added
- Updated Fpdi library to version 2.2

## [1.1.0] - 2018-01-05
### Added
- Configuration for paper orientation and size 

## [1.0.3] - 2017-09-13
### Fixed
- Fixed issue that pdf background file for subsequent pages will also be taken for first page

## [1.0.2] - 2017-08-09
### Fixed
- PDF author is now set correctly

## [1.0.1] - 2017-06-06
### Fixed
- Corrected library dependencies

## [1.0.0] - 2017-05-06
### Added
- Initial version of the library
