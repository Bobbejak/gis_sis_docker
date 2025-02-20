Step	Action
1	Create Git repo, export config, dump DB, get module list
2	Create Docker container and verify it works
3	Run startup script, check functionality
4	Import DB dump (if necessary) and test again
5	Integrate startup script into container build
6	Rebuild container and verify everything works
7	Copy Docker container to PC B
8	Pull Git on PC B
9	Start container on PC B and verify functionality

To start a container and migrate successfully
From previous site, move:
1. contrib and custom modules and themes to 'drupal/' folder. Place folders of themes and modules in appropriate folders within 'drupal/'.
2. config/sync files to 'import/config/sync/'
3. db sql dump file to db_backups.
4. images and other files to 'drupal/sites/default/files'.
5. settings.php from startup_files folder to 'drupal/sites/default/'. Ensure settings match those in docker-compose.yml.