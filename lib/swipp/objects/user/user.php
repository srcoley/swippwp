<?php
namespace Swipp\api;

class user extends objectBase
{
    public $profileSource;

    public $firstName;
    public $lastName;
    public $imageUrl;
    public $urlAvatar;
    public $thumbnailUrl;
    public $emailAddress;

    public $following;
    public $verified;
    public $userGuid;
    public $userName;

    public $shareFlag;
    public $notificationFlag;

    public $f;

    public $_readOnly = array("userGuid","profileSource");

    public function isFBShareActive()
    {

    }

    public function isTWShareActive()
    {

    }

    public function isEmailNotificationActive()
    {

    }

    public function isAppNotificationActive()
    {

    }

}
