<?php
/**
 * Handles the update configuration and hooks for the plugin.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

use Jcore\Update\Config\UpdateConfig;
use Jcore\Update\Hooks\PluginUpdateHooks;
use Jcore\Update\Support\PluginHelper;

// Exit if ABSPATH is not defined.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config = new UpdateConfig(
	pluginFile: JCORE_RUNNER_PLUGIN_FILE,
	slug: 'jcore-runner',
	version: PluginHelper::getVersion( JCORE_RUNNER_PLUGIN_FILE ),
	apiBaseUrl: 'https://update.jcore.fi/v1',
);
( new PluginUpdateHooks( $config ) )->register();
