# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/)
and this project adheres to [Semantic Versioning](https://semver.org/).

## [5.0.0] - 2025-01-10
### Added
- Merger for Zugferd PDFs, i.e. PDFs with XML attachments
### Changed
- TCPDF to Fpdf library for merging as default (both libraries supported now)
- Several public interfaces to support type hints and make purpose clearer
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
