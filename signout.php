<?php
require('includes/shared.inc.php');


$dbh = new DatabaseHandler;
$snh = new Session($dbh);


// Handle logging out
if($snh->authKeyCompare()) {
    $snh->sessionLogout();
}

$usr = new User($dbh, $snh->get('user_id'));


$pge = new Template();
$pge->set('page-title', 'Testing - Sign Out')
    ->setCondition(
        'user-links',
        '<li><a href="signin.php" id="sign-in">Sign In</a></li>',
        '<li><a href="signout.php" id="sign-out">Sign Out</a></li>',
        is_null($usr->get('id')),
        true
    )
    ->set('section-title', 'Sign Out', true)
    ->set('section-contents', '<form class="page-form" method="post" action="signout.php">
                <input type="hidden" name="auth_key" value="{{auth-key}}" />
                <p>Please confirm your sign out request.</p>
                <button>Sign Out</button>
            </form>')
    ->set('auth-key', $snh->get('auth_key'))
    ->display('page.tpl');