# JCORE Script Runner Plugin #

A WordPress plugin to easily allow manual running of scripts for maintenance and utility.

### How do I get set up? ###

Install the plugin in your project. Either as a submodule, or just through the zip file.

Wherever you want in you code (Project theme, separate plugin) add the functions you want to run. Any output you want shown to the user should be echoed.

Use the `jcore_runner_functions` filter to add your function to the runner. The filter gives you one array that you can add the functions to. Use a unique ID as key and add an array with: 
`
array(
    'title' => 'The title shown in the runner',
    'callback' => '\namespace\name_of_function',
) 
`