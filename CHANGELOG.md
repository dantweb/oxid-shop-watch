# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-29

### Added

- Initial release of Shop Watch - E2E Testing API for OXID eShop 7.4+
- HTTP API endpoint for database state verification (`/shopwatch/assume`)
- Two-factor authentication (IP whitelist + API key)
- SQL injection prevention with prepared statements
- Operator strategies for value comparison:
  - Equality operators (`==`, `!=`)
  - Comparison operators (`>`, `<`, `>=`, `<=`)
  - LIKE operators (`%like%`, `like%`, `%like`)
  - NULL check operators (`IS NULL`, `IS NOT NULL`)
- CIDR notation support for IP address ranges
- Comprehensive audit logging
- Rate limiting support
- Full test suite (60 unit tests)
- GitHub Actions CI/CD:
  - Tests on push/PR (PHP 8.2 & 8.3)
  - Weekly scheduled tests (Monday 6 AM UTC)
- Comprehensive documentation with Playwright and cURL examples

[1.0.0]: https://github.com/dantweb/oxid-shop-watch/releases/tag/v1.0.0
