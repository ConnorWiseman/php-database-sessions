<?php
require('includes/shared.inc.php');


$dbh = new DatabaseHandler;
$snh = new Session($dbh);
$usr = new User($dbh, $snh->get('user_id'));


// Handle account creation
if($snh->authKeyCompare()) {
    if($usr->create()) {
        $snh->sessionLogin($usr->get('id'));
    }
}

$pge = new Template();
$pge->set('page-title', 'Testing - Create an account')
    ->setCondition(
        'user-links',
        '<li><a href="signin.php" id="sign-in">Sign In</a></li>',
        '<li><a href="signout.php" id="sign-out">Sign Out</a></li>',
        is_null($usr->get('id')),
        true
    )
    ->set('section-title', 'Create an account', true)
    ->set('section-contents', '<form class="page-form" method="post" action="create.php">
                <input type="hidden" name="auth_key" value="{{auth-key}}" />
                <input type="text" name="email" placeholder="Email" />
                <input type="text" name="username" placeholder="Username" />
                <input type="password" name="password" placeholder="Password" />
                <button>Create Account</button>
            </form>', true)
    ->set('auth-key', $snh->get('auth_key'))
    ->display('page.tpl');