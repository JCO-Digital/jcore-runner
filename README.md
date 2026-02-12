# JCORE Script Runner Plugin

A powerful WordPress utility for managing, running, and scheduling maintenance scripts through a clean administrative interface. It handles long-running processes via pagination, provides real-time logging, and supports data exports.

## Features

- **Admin UI**: Easily run scripts from the WordPress "Tools" menu.
- **Pagination**: Handle heavy tasks by splitting execution into multiple requests to avoid timeouts.
- **Scheduling**: Built-in support for WP-Cron with customizable intervals.
- **Logging**: Automatically captures `echo` output and saves it to daily log files.
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

## Hooks & Filters

- `jcore_runner_functions`: Main filter to register scripts.
- `jcore_runner_title`: Change the page title in Admin.
- `jcore_runner_menu`: Change the menu label.
- `jcore_runner_cron_schedules`: Add custom cron intervals.

## Requirements

- PHP 8.0+
- WordPress 6.0+
