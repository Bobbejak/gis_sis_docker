<?php

$databases['default']['default'] = array (
  'database' => 'gis_sis',
  'username' => 'gis_sis_user',
  'password' => '240885MP',
  'host' => 'mariadb',  // ✅ Ensure it connects via TCP/IP, not a Unix socket
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
