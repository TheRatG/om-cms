<?php
namespace TheRat\OmCms\AdminBundle\Admin;

use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;

class UserAdmin extends BaseUserAdmin
{
    protected $baseRouteName = 'admin_sonata_user_user';
}
