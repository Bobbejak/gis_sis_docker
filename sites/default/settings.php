<?php

$databases['default']['default'] = array (
  'database' => 'gis_sis',
  'username' => 'gis_sis_user',
  'password' => 'user_240885MP',
  'host' => 'db',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
);


  

// Hash salt (required for security)
$settings['hash_salt'] = 'random-generated-string';

// Allow file system writes
$settings['file_private_path'] = '/var/www/html/sites/default/private';
$settings['config_sync_directory'] = '/var/www/html/config/sync';

$settings['update_free_access'] = FALSE;

// Ensure correct permissions
$settings['file_scan_ignore_directories'] = ['node_modules', 'bower_components'];
