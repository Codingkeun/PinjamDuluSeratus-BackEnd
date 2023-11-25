<?php

/**
 * Deployer Script
 * 
 * @author Gema Aji Wardian
 */

namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'pinjamduluseratus_backend');

// Project repository
set('repository', 'git@github.com:Codingkeun/PinjamDuluSeratus-BackEnd.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// Shared files/dirs between deploys 
add('shared_files', ['.env']);
add('shared_dirs', ['storage', 'public/assets/']);

// Writable dirs by web server 
add('writable_dirs', []);

// Hosts
host('development')
    ->hostname('51.161.11.156')
    ->stage('dev')
    ->port(22)
    ->user('ubuntu')
    ->set('branch', 'master')
    ->set('deploy_path', '/var/www/oeltimacreation/apps/pinjamdulustatus-api');


// Tasks

task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    //    'deploy:vendors-dev',
    //    'deploy:phpunit',
    //    'deploy:security-checker',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:symlink',
    'cleanup',
])->desc('Deploy your project');

task('reload:php-fpm', function () {
    run('sudo /usr/sbin/service php8.1-fpm reload');
});

after('deploy', 'reload:php-fpm');
