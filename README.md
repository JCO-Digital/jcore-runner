# JCORE Script Runner Plugin

A powerful WordPress utility for managing, running, and scheduling maintenance scripts through a clean administrative interface. It handles long-running processes via pagination, provides real-time logging, and supports data exports.

## Features

- **Admin UI**: Easily run scripts from the WordPress "Tools" menu.
- **Pagination**: Handle heavy tasks by splitting execution into multiple requests to avoid timeouts.
- **Scheduling**: Built-in support for WP-Cron with customizable intervals.
- **Logging**: Automatically captures `echo` output and saves it to daily log files, which can be read and downloaded in the admin.
- **Exports**: Built-in `Export` class to generate CSV or JSON files.
- **Input Fields**: Support for user inputs including text, select (with Select2), dates, and file uploads.

## Installation

Install the plugin in your WordPress `wp-content/plugins` directory.

```bash
cd wp-content/plugins
git clone https://github.com/JCO-Digital/jcore-runner.git
```

## Basic Usage

To add a script to the runner, use the `jcore_runner_functions` filter.

### 1. Register your script

```php
add_filter('jcore_runner_functions', function($scripts) {
    $scripts['my_utility_script'] = [
        'title'    => 'Update Product Meta',
        'callback' => 'my_namespace\update_products_callback',
        'input'    => [
            'batch_size' => [
                'title'   => 'Items per batch',
                'type'    => 'number',
                'default' => 50,
            ],
        ],
    ];
    return $scripts;
});
```

### 2. The Callback Function

The callback receives an instance of `Jcore\Runner\Arguments`. Any content you `echo` will be displayed in the runner console and saved to logs.

```php
namespace my_namespace;

use Jcore\Runner\Arguments;

function update_products_callback(Arguments $args) {
    // Access user input
    $batch_size = $args->input['batch_size'] ?? 10;

    // Access persistent data between pages
    $current_page = $args->page; // Starts at 1

    echo "Processing batch $current_page...\n";

    // Perform your logic here...

    // To continue to the next page (pagination):
    if ($current_page < 5) {
        $args->set_next_page();
        // Pass data to the next iteration
        $args->data['processed_count'] = ($args->data['processed_count'] ?? 0) + $batch_size;
    }

    return $args;
}
```

## Advanced Usage

### Input Types

The plugin supports various input types for the admin UI:

- `text`, `number`, `email`: Standard HTML inputs.
- `select`: Uses Select2. Requires an `options` array.
- `date`: Uses jQuery UI Datepicker.
- `file`: Allows uploading files which are passed to the callback.

```php
'input' => [
    'category' => [
        'title'   => 'Select Category',
        'type'    => 'select',
        'options' => [
            'news' => 'News',
            'tech' => 'Technology',
        ],
        'default' => 'news',
    ],
    'start_date' => [
        'title' => 'Start From',
        'type'  => 'date',
    ]
]
```

### Exporting Data

You can generate files (CSV/JSON) during execution:

```php
function my_export_script(Arguments $args) {
    $args->export->set_extension('csv');
    $args->export->add_row(['ID', 'Name', 'Status']);
    $args->export->add_row([1, 'Test Item', 'Complete']);

    return $args;
}
```

### Status Fields

You can display persistent status information in the runner UI that updates in real-time between paginated requests.

```php
// 1. Register the status field
add_filter('jcore_runner_status', function($status) {
    $status['progress_bar'] = 'Current Progress';
    return $status;
});

// 2. Update it in your callback
function my_paginated_script(Arguments $args) {
    $total = 100;
    $current = $args->page * 10;

    // This will update the 'Current Progress' section in the UI
    $args->return['progress_bar'] = "$current / $total items processed";

    if ($current < $total) {
        $args->set_next_page();
    }
    return $args;
}
```

### Scheduling (Cron)

Scripts registered via `jcore_runner_functions` can be scheduled directly from the plugin table in the WordPress admin. The plugin adds custom intervals:

- `every_minute`
- `jcore_hourly`
- `jcore_daily`
- `jcore_weekly`

The table shows the interval and next run of each scheduled script.

### Logs and Exports

The Logs column links to a log viewer for each script, which lists its log files and shows the selected one. Large logs are cut to the last 512 KB, which can be changed with the `jcore_runner_log_max_bytes` filter.

Logs and exports are downloaded through `admin-post.php`, for users with `manage_options`, so downloads work on servers that block direct access to `.log` files. The `logs` folder in uploads gets an `.htaccess` that denies web access on Apache. On nginx, add a rule for `/wp-content/uploads/jcore-runner/logs/` yourself.

### WP-CLI

Scripts registered via `jcore_runner_functions` are also available through WP-CLI.

```bash
wp runner list
wp runner run my_utility_script --batch_size=50
wp runner my_utility_script --batch_size=50
```

The CLI runner supports paginated scripts and accepts registered input fields as `--field=value` arguments. Use `--max-pages=<number>` to cap paginated runs, and `--page=<number>` to resume from a specific page.

## Hooks & Filters

- `jcore_runner_functions`: Main filter to register scripts.
- `jcore_runner_title`: Change the page title in Admin.
- `jcore_runner_menu`: Change the menu label.
- `jcore_runner_cron_schedules`: Add custom cron intervals.
- `jcore_runner_log_max_bytes`: Max bytes of a log shown in the log viewer (default 512 KB).

## Requirements

- PHP 8.1+
- WordPress 6.7+ (tested up to 7.0)

## Releases

Releases are made with [foonver](https://github.com/foonly/foonver) from conventional commits. The version is synced to `jcore-runner.php`, `readme.txt` and `package.json`, and the changelog is written to the `== Changelog ==` section of `readme.txt`, which WordPress shows in the plugin information popup. Do not edit that section by hand.

## Changelog

See the changelog section of [readme.txt](readme.txt) for a detailed version history.
