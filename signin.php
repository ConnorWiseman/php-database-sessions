<?php
require('includes/shared.inc.php');


$dbh = new DatabaseHandler;
$snh = new Session($dbh);
$usr = new User($dbh, $snh->get('user_id'));


// Handle logging in
if($snh->authKeyCompare()) {
    echo 'key is good';
    if($usr->authenticate()) {
        echo 'user authenticated';
        $snh->sessionLogin($usr->get('id'));
    }
}


$pge = new Template();
$pge->set('page-title', 'Testing - Sign In')
    ->setCondition(
        'user-links',
        '<li><a href="signin.php" id="sign-in">Sign In</a></li>',
        '<li><a href="signout.php" id="sign-out">Sign Out</a></li>',
        is_null($usr->get('id')),
        true
    )
    ->set('section-title', 'Sign In', true)
    ->set('section-contents', '<form class="page-form" method="post" action="signin.php">
                <input type="hidden" name="auth_key" value="{{auth-key}}" />
                <input type="text" name="email" placeholder="Email" />
                <input type="password" name="password" placeholder="Password" />
                <button>Sign In</button>
            </form>')
    ->set('auth-key', $snh->get('auth_key'))
    ->display('page.tpl');