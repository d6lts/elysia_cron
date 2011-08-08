<?php

/**
 * @file
 * Handles incoming requests to fire off regularly-scheduled tasks (cron jobs).
 */

if (!file_exists('includes/bootstrap.inc') && preg_match('@^(.*)[\\\\/]sites[\\\\/][^\\\\/]+[\\\\/]modules[\\\\/]([^\\\\/]+[\\\\/])?elysia(_cron)?$@', getcwd(), $r))
  chdir($r[1]);

include_once './includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

global $base_url;
$base_url = str_replace('/'.drupal_get_path('module', 'elysia_cron'), '', $base_url);

if (function_exists('elysia_cron_run'))
  elysia_cron_run();
else
  drupal_cron_run();
