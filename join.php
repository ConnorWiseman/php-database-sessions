<?php
require('includes/shared.inc.php');


$dbh = new DatabaseHandler;
$dbh->connect()
    ->useDatabase('test');
$snh = new Session($dbh);


$tpl = new Template;
$tpl->set('page-title', 'Service Name &raquo; Join')
    ->loadUnique('user-links', 'templates/user-links-guest.tpl')
    ->setUnique('section-title', 'Join')
    ->loadUnique('section-contents', 'templates/join-form.tpl')
    ->set('auth-key', $snh->getAuthKey())
    ->display('page.tpl');