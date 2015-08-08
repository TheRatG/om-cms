<?php

namespace OmCms\AdminBundle\Admin;

use Sonata\UserBundle\Admin\Model\GroupAdmin as BaseGroupAdmin;

class GroupAdmin extends BaseGroupAdmin
{
    protected $baseRouteName = 'admin_sonata_user_group';
}
