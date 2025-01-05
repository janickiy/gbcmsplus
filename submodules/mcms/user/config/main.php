<?php

use mcms\user\Module;

return [
    'id' => 'users',
    'preload' => true,
    'class' => 'mcms\user\Module',
    'name' => 'app.common.module_users',
    'menu' => [
        'icon' => 'fa-lg fa-fw icon-user',
        'label' => 'users.menu.users',
        'events' => [
            \mcms\user\components\events\EventRegistered::class,
            \mcms\user\components\events\EventRegisteredHandActivation::class,
        ],
        'items' => [
            ['label' => 'users.menu.control', 'url' => ['/users/admin']],
            [
                'label' => 'users.menu.user_list',
                'url' => ['/users/users/list'],
                'events' => [
                    \mcms\user\components\events\EventRegistered::class,
                    \mcms\user\components\events\EventRegisteredHandActivation::class,
                ]
            ],
        ]
    ],
    'messages' => '@mcms/user/messages',
    'events' => [
        \mcms\user\components\events\EventRegistered::class,
        \mcms\user\components\events\EventRegisteredHandActivation::class,
        \mcms\user\components\events\EventPasswordChanged::class,
        \mcms\user\components\events\EventReferralRegistered::class,
        \mcms\user\components\events\EventStatusChanged::class,
        \mcms\user\components\events\EventActivationCodeSended::class,
        \mcms\user\components\events\EventPasswordSended::class,
        \mcms\user\components\events\EventPasswordGenerateLinkSended::class,
        \mcms\user\components\events\EventAuthLoggedIn::class,
        \mcms\user\components\events\EventAuthLoggedOut::class,
        \mcms\user\components\events\EventUserCreated::class,
        \mcms\user\components\events\EventUserUpdated::class,
        \mcms\user\components\events\EventUserBlocked::class,
        \mcms\user\components\events\EventUserApproved::class,
        \mcms\user\components\events\EventUserApprovedWithoutReferrals::class,
    ],
    'apiClasses' => [
        'getOneUser' => \mcms\user\components\api\UserList::class,
        'editUser' => \mcms\user\components\api\UserEdit::class,
        'changeUserPassword' => \mcms\user\components\api\UserChangePassword::class,
        'roles' => \mcms\user\components\api\Roles::class,
        'user' => \mcms\user\components\api\User::class,
        'userRelation' => \mcms\user\components\api\relations\User::class,
        'usersByRoles' => \mcms\user\components\api\UsersByRoles::class,
        'rolesByUserId' => \mcms\user\components\api\RolesByUserId::class,
        'auth' => \mcms\user\components\api\Auth::class,
        'notAvailableUserIds' => \mcms\user\components\api\NotAvailableUserIds::class,
        'userBack' => \mcms\user\components\api\UserBack::class,
        'userLink' => \mcms\user\components\api\UserLink::class,
        'statuses' => \mcms\user\components\api\Statuses::class,
        'referrals' => \mcms\user\components\api\Referrals::class,
        'userParams' => \mcms\user\components\api\UserParams::class,
        'userTelegram' => \mcms\user\components\api\UserTelegram::class,
        'loginForm' => \mcms\user\components\api\LoginForm::class,
        'signupForm' => \mcms\user\components\api\SignupForm::class,
        'passwordResetRequestForm' => \mcms\user\components\api\PasswordResetRequestForm::class,
        'resetPasswordForm' => \mcms\user\components\api\ResetPasswordForm::class,
        'contactForm' => \mcms\user\components\api\ContactForm::class,
        'badgeCounters' => \mcms\user\components\api\BadgeCounters::class,
    ],
    'fixtures' => require(__DIR__ . '/fixtures.php')
];