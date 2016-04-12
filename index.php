<?php
require('includes/shared.inc.php');


$dbh = new DatabaseHandler;
$snh = new Session($dbh);
$usr = new User($dbh, $snh->get('user_id'));


$pge = new Template();
$pge->set('page-title', 'Testing')
    ->setCondition(
        'user-links',
        '<li><a href="signin.php" id="sign-in">Sign In</a></li>',
        '<li><a href="signout.php" id="sign-out">Sign Out</a></li>',
        is_null($usr->get('id')),
        true
    )
    ->set('section-title', 'Session and User Information', true)
    ->set('section-contents', $snh->sessionInfo() . $usr->userInfo(), true)
    ->set('auth-key', $snh->get('auth_key'))
    ->display('page.tpl');