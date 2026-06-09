# Changelog

## 4.3.0 (2026-06-09)

#### Features

- update: Added jcore-update integration (1c0d89d)

#### Continuous Integration

- add release pipeline and project configuration (730e9ab)

## v4.2.0 (2026-05-27)

#### Features

- cli: add WP-CLI integration for runner scripts (f000036)

#### Bug Fixes

- ui: satisfy admin UI checks (8828754)
- ui: clean up runner admin escaping (af7cb24)
- cli: improve error handling and validation for runner scripts (a1889e1)

#### Refactor

- ui: update and modernize runner interface (a12a9d0)

#### Documentation

- update README with detailed usage and feature documentation (dde361a)

## v4.1.0 (2025-01-24)

#### Features

- switch to pnpm (7b38dfb)

#### Bug Fixes

- versionsync, so that versions will be correct in WP (d53c429)

### v4.0.1 (2024-08-23)

#### Bug Fixes

- Fixed a bug where classes were not correctly handled in checking for plain objects. (9d31235)

## v4.0.0 (2024-08-23)

#### Refactor

- Make the objectToFormData function recursive to handle nested objects ♻️ (01bcc0f)

#### Maintenance

- formatting 🎨 (ec1407f)

## v3.1.1-rc.0 (2024-08-22)

#### Refactor

- Use FormData instead of JSON for data, this allows for files to be sent. ♻️ (3df8768)

### v3.1.0-rc.4 (2024-08-21)

#### Bug Fixes

- Also allow unscheduling of cron jobs... 🐛 (59b6099)

### v3.1.0-rc.3 (2024-08-21)

#### Bug Fixes

- Pass the key to the schedule action callback URL 🐛 (8553fb9)

### v3.1.0-rc.2 (2024-08-21)

#### Bug Fixes

- rename the cron schedules to avoid conflicts 🐛 (fafac40)

### v3.1.0-rc.1 (2024-08-21)

#### Bug Fixes

- fixed redeclaration of function :bug: (2d5c3c9)

## v3.1.0-rc.0 (2024-08-21)

#### Refactor

- Cron schedules refactored and runner table refactored ♻️ (e591112)

### v3.0.9 (2024-08-20)

#### Refactor

- cron: Ability to select CRON schedule (1462dec)

### v3.0.8 (2024-06-07)

#### Bug Fixes

- ui: Fixed an issue with loadin of inputs. 🐛 (1b881a7)

### v3.0.7 (2024-06-06)

### Misc
- Code cleanup (7c81d4b)
- Fix cron arguments, and clean up logic. (881fa92)
- Changed cron runner to single script schedule. (0fbb961)

### v3.0.6 (2024-04-09)

#### Maintenance

- uppercase type (b76100e)

### v3.0.5 (2024-04-09)

#### Features

- correctly handle the radio/checkbox values (9bde38c)

### v3.0.4 (2024-04-09)

#### Bug Fixes

- removed inline style forcing from old code (4c180bc)

### v3.0.3 (2024-04-09)

#### Maintenance

- minor styling fix (4487238)

### v3.0.2 (2024-04-09)

#### Features

- fixed an issue with checkbox styling (ff4ec1d)

### v3.0.1 (2024-04-09)

#### Maintenance

- download only, no filename (ca479e7)

## v3.0.0 (2024-04-04)

#### Features

- Export now supports CSV + some other minor improvements (d3df814)

#### Bug Fixes

- PHP 8.0 compatibility fix (7010e71)

## v2.2.0 (2024-03-28)

#### Refactor

- ui: Refactored UI + Inputs to allow for more varied inputs :sparkles: (5b3fa76)

#### Maintenance

- formatting (c94fd2d)

### Misc
- Update rest-runner.php. Disable Warnings (cd00e1f)

### v2.1.1 (2024-03-18)

#### Features

- Title updated to reflect selected runners Title (9d24347)

#### Maintenance

- release version 2.1.1 (96fbdcf)

## v2.1.0 (2024-02-09)

### Misc
- Release candidate. (ee2f042)
- Everything should work now, UI is still not there. (19826bd)
- Code should work, UI still sucks. (8bdc219)
- Cron functionality, added log file writing. (363f0ec)
- Basic cron management. (c44970d)

### v2.0.1 (2024-01-25)

#### Bug Fixes

- proper checking for input values (40059d7)

## v2.0.0 (2024-01-12)

#### Bug Fixes

- Fix warning for missing 'input' section. (d0e282e)

#### Maintenance

- Cleanup and final tweaks before release. (cbb26fd)

### Breaking
- Custom script arguments now uses a class to improve usability. (8e79f82)

### v1.1.2 (2024-01-02)

### Misc
- Added newline between outputs. (368b041)

### v1.1.1 (2024-01-02)

#### Maintenance

- Prettier (a10425e)
- Added version sync from package.json (37caa42)
- file exporter now manages JSON data (c181bf1)

### Feature
- Added input field support. (5b93b16)
- Added export class for exporting data to file. (2716628)

### Misc
- Version update (1462084)

### v1.0.2 (2023-12-21)

#### Maintenance

- Add versioning scripts. (3b49bc6)
- update composer installer (2a6623d)
- formatting and npm scaffolding. (2371820)
- updated package name in composer. (6facd91)

### v1.0.1 (2023-11-09)

#### Bug Fixes

- rest: Some callable checks improvements and respond correctly. (79d3baf)

## v1.0.0 (2023-11-09)

### Misc
- Added composer json (a4f91ca)
- Return value support (a3fb2e8)
- Scroll output and styling. (122b435)
- Readme fix (d220f17)
- Added files. (7e5ea4f)
- Initial commit (a359617)

