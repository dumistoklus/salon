<?php
namespace Orgup\Modules\Index\Administration\Users;

use \Orgup\Modules\Index\Administration\AdministrationModuleBuilder;
use Orgup\Application\Registry;
use \Orgup\Common\Mod;
use \Orgup\Common\BanUser;
use \Orgup\Common\DBAccess\ActiveRecord;
use \Orgup\DataModels\Index\Administration\Users\Filter;

class Users extends AdministrationModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Administration\Users\Users
     */
    protected $Data;

    const USERS_PER_PAGE = 20;

    private $users = array();
    private $count;
    /**
     * @var \Orgup\DataModels\Index\Administration\Users\Filter
     */
    private $Filter;
    private $currentPage;

    public function run()
    {


        $this->Filter = $this->Data->filter();

        $this->currentPage = $this->Filter->page();

        if($this->Filter->isUserBan())
        {
            $this->banUser();
        }

        $this->getUsers();
        $this->Data->setUsers( $this->users );
        $this->Data->add_paginator($this->count, $this->currentPage, self::USERS_PER_PAGE);
    }

    private function banUser()
    {
        $banned = BanUser::ban($this->Filter->userForBan());

        if($banned)
        {
            $this->Data->add_notification('user_banned', 'admin');
        }
        else
        {
            $this->Data->add_error('user_not_banned', 'admin');
        }
    }

    private function getUsers()
    {
        $Users = new UsersList($this->Filter);
        $this->count = $Users->countOfAll();
        $Users->start($this->currentPage * self::USERS_PER_PAGE - self::USERS_PER_PAGE)->limit(self::USERS_PER_PAGE);

        $this->users = $Users->find();
    }

}

class UsersList extends ActiveRecord
{

    function __construct(Filter $Filter)
    {
        $this->sql("SELECT u.id_user, u.username, u.email, u.city_id, u.active, u.created, u.last_visit FROM `users` AS u");
        $this->from('users', 'u');

        $this->orderBy('username', ActiveRecord::ASC_ORDER);

        if($Filter->isSearch())
        {
            if($Filter->userName() !== '')
            {
                $this->where('username', $Filter->userName(), ActiveRecord::WHERE_LIKE);
            }
            
            if($Filter->cityId() != 0)
            {
                $this->where('u.city_id', $Filter->cityId(), ActiveRecord::WHERE_EQUALS, false);
            }
        }
    }

    public function iterator()
    {
        return $this->asArray();
    }
}