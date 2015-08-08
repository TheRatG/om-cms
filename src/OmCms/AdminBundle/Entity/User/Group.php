<?php
namespace OmCms\AdminBundle\Entity\User;

use Sonata\UserBundle\Entity\BaseGroup;

class Group extends BaseGroup
{
    /**
     * @var integer
     */
    protected $id;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        parent::__construct('admin', []);
    }
}
