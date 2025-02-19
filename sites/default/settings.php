<?php

$databases['default']['default'] = array (
  'database' => getenv('DRUPAL_DATABASE_NAME') ?: 'gis_sis',
  'username' => getenv('DRUPAL_DATABASE_USER') ?: 'gis_sis_user',
'password' => getenv('DRUPAL_DATABASE_PASSWORD') ?: 'user_240885MP',
  'host' => getenv('DRUPAL_DATABASE_HOST') ?: 'db',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
);

  

// Hash salt (required for security)
$settings['hash_salt'] = 'random-generated-string';

// Allow file system writes
$settings['file_private_path'] = '../private';
$settings['config_sync_directory'] = '../config/sync';
$settings['update_free_access'] = FALSE;

// Ensure correct permissions
$settings['file_scan_ignore_directories'] = ['node_modules', 'bower_components'];
