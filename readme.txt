=== JCORE Script Runner ===
Contributors: jcodigital
Tags: maintenance, scripts, cron, wp-cli, tools
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 4.5.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Run, schedule and log maintenance scripts from the Tools menu, WP-Cron or WP-CLI.

== Description ==

JCORE Script Runner gives developers a place to register maintenance and utility scripts, and gives site administrators a clean interface to run them. Long-running work is split into paginated requests so it never hits a timeout, all output is logged, and scripts can produce downloadable exports.

**For administrators**

* Scripts are listed under Tools, with search, and each one runs on its own page with a live console.
* Scripts can take input: text, numbers, email, select lists, dates and file uploads.
* Any script can be scheduled with WP-Cron directly from the list, using built-in every-minute, hourly, daily and weekly intervals.
* Every run is captured to a daily log file, which can be read and downloaded from the admin.

**For developers**

* Register scripts with the `jcore_runner_functions` filter. Each script is a title, a callback and an optional list of input fields.
* The callback receives a `Jcore\Runner\Arguments` object with the user input, the current page, data carried between pages, and an export helper.
* Call `set_next_page()` on the arguments to continue in a new request.
* Show live status fields in the interface with the `jcore_runner_status` filter.
* Write CSV or JSON files with the export helper, offered for download when the run finishes.
* Run the same scripts from the command line with `wp runner list` and `wp runner run <script>`, passing input fields as `--field=value`.

**Filters**

* `jcore_runner_functions` registers scripts.
* `jcore_runner_status` registers status fields.
* `jcore_runner_title` and `jcore_runner_menu` change the page and menu titles.
* `jcore_runner_cron_schedules` adds cron intervals.
* `jcore_runner_log_max_bytes` sets how much of a log the log viewer shows.

== Installation ==

1. Install the plugin through Composer with `composer require jcodigital/jcore-runner`, or upload the release zip on the Plugins screen.
2. Activate the plugin.
3. Register your scripts with the `jcore_runner_functions` filter in a theme or plugin, then open Tools, JCORE Script Runner.

Updates are delivered through the JCORE update service.

== Frequently Asked Questions ==

= How do I register a script? =

Add it in the `jcore_runner_functions` filter. Each entry is keyed by an ID and has a `title` and a `callback`, for example `$scripts['my_script'] = array( 'title' => 'My script', 'callback' => 'my_namespace\my_callback' );`. The callback receives and returns a `Jcore\Runner\Arguments` object. See the README on GitHub for full examples.

= How do I avoid timeouts on big jobs? =

Do a slice of the work, call `$args->set_next_page()`, and return. The runner starts a new request for the next page and passes `$args->data` along.

= Where are the logs? =

Output from every run is appended to a daily log file in the uploads directory, and shown live in the console while the script runs. Open them from the Logs column in the script list to read or download them. Downloads go through WordPress, so they work even when the server blocks direct access to `.log` files.

= Can I run scripts without the browser? =

Yes. Use `wp runner list` to see registered scripts and `wp runner run <script>` to run one. Paginated scripts are followed to the end, or capped with `--max-pages`.

== Changelog ==

= 4.5.0 (2026-10-12) =

* Feature: view and download logs and exports in the admin
* Feature: show the interval of scheduled scripts
* Maintenance: i18n - regenerate POT and update Finnish translations

= v4.4.5 (2026-09-29) =

* Documentation: readme - add readme.txt with changelog and move changelog generation there
* Build: deps - update jcore-update to 1.7.1
* CI: Add Slack notifications to release workflow

= v4.4.4 (2026-09-16) =

* Build: deps - update PHP requirement to 8.3 and foonver action

= v4.4.3 (2026-06-23) =

* Fix: actually deploy
* Fix: use absolute plugin include paths

= v4.4.2 (2026-06-09) =

* Build: composer - rename package to jcodigital/jcore-runner

= v4.4.1 (2026-06-09) =

* Style: metadata - update plugin header requirements

= v4.4.0 (2026-06-09) =

* Feature: update - Added jcore-update integration
* Build: npm - specify package manager version
* CI: add release pipeline and project configuration

= v4.2.0 (2026-05-27) =

* Feature: cli - add WP-CLI integration for runner scripts
* Fix: ui - satisfy admin UI checks
* Fix: ui - clean up runner admin escaping
* Fix: cli - improve error handling and validation for runner scripts
* Refactor: ui - update and modernize runner interface
* Documentation: update README with detailed usage and feature documentation

= v4.1.0 (2025-01-24) =

* Feature: switch to pnpm
* Fix: versionsync, so that versions will be correct in WP

= v4.0.1 (2024-08-23) =

* Fix: Fixed a bug where classes were not correctly handled in checking for plain objects.

= v4.0.0 (2024-08-23) =

* Fix: Also allow unscheduling of cron jobs... 🐛
* Fix: Pass the key to the schedule action callback URL 🐛
* Fix: rename the cron schedules to avoid conflicts 🐛
* Fix: fixed redeclaration of function :bug:
* Refactor: Make the objectToFormData function recursive to handle nested objects ♻️
* Refactor: Use FormData instead of JSON for data, this allows for files to be sent. ♻️
* Refactor: Cron schedules refactored and runner table refactored ♻️
* Maintenance: formatting 🎨

= v3.0.9 (2024-08-20) =

* Refactor: cron - Ability to select CRON schedule

= v3.0.8 (2024-06-07) =

* Fix: ui - Fixed an issue with loadin of inputs. 🐛

= v3.0.7 (2024-06-06) =

* Code cleanup
* Fix cron arguments, and clean up logic.
* Changed cron runner to single script schedule.

= v3.0.6 (2024-04-09) =

* Maintenance: uppercase type

= v3.0.5 (2024-04-09) =

* Feature: correctly handle the radio/checkbox values

= v3.0.4 (2024-04-09) =

* Fix: removed inline style forcing from old code

= v3.0.3 (2024-04-09) =

* Maintenance: minor styling fix

= v3.0.2 (2024-04-09) =

* Feature: fixed an issue with checkbox styling

= v3.0.1 (2024-04-09) =

* Maintenance: download only, no filename

= v3.0.0 (2024-04-04) =

* Feature: Export now supports CSV + some other minor improvements
* Fix: PHP 8.0 compatibility fix

= v2.2.0 (2024-03-28) =

* Refactor: ui - Refactored UI + Inputs to allow for more varied inputs :sparkles:
* Maintenance: formatting
* Update rest-runner.php. Disable Warnings

= v2.1.1 (2024-03-18) =

* Feature: Title updated to reflect selected runners Title
* Maintenance: release version 2.1.1

= v2.1.0 (2024-02-09) =

* Release candidate.
* Everything should work now, UI is still not there.
* Code should work, UI still sucks.
* Cron functionality, added log file writing.
* Basic cron management.

= v2.0.1 (2024-01-25) =

* Fix: proper checking for input values

= v2.0.0 (2024-01-12) =

* Fix: Fix warning for missing 'input' section.
* Maintenance: Cleanup and final tweaks before release.
* Breaking: Custom script arguments now uses a class to improve usability.

= v1.1.2 (2024-01-02) =

* Added newline between outputs.

= v1.1.1 (2024-01-02) =

* Maintenance: Prettier
* Maintenance: Added version sync from package.json
* Maintenance: file exporter now manages JSON data
* Feature: Added input field support.
* Feature: Added export class for exporting data to file.
* Version update

= v1.0.2 (2023-12-21) =

* Maintenance: Add versioning scripts.
* Maintenance: update composer installer
* Maintenance: formatting and npm scaffolding.
* Maintenance: updated package name in composer.

= v1.0.1 (2023-11-09) =

* Fix: rest - Some callable checks improvements and respond correctly.

= v1.0.0 (2023-11-09) =

* Added composer json
* Return value support
* Scroll output and styling.
* Readme fix
* Added files.
* Initial commit
