<?php

namespace Orgup\Modules\Index\Administration\Users;
use \Orgup\Application\Registry;
use \Orgup\Application\Redirect;
use \Orgup\Common\Administration\RulesManager;
use \Orgup\Modules\Index\Administration\AdministrationModuleBuilder;

class Rules extends AdministrationModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Administration\Users\Rules
     */
    protected $Data;

    public function run() {

        if ( $this->Routing->route_get('mess') == 'changes_updated' ) {
            $this->Data->add_notification('saved', 'admin');
            $this->Ways->delete_param('mess');
        }

        $userId =  $this->userId();

        $RulesManager = new RulesManager($userId, $this->Routing->post() );

        // если права пользователя
        if ( $this->show() )
        {
            $Users = new UsersManager();
            if (  $userId > 0 && $Users->is_user_exist_with_id(  $userId ) ) {

                $this->Data->setUserName( $Users->get_username(  $userId ) );
                $this->Data->setUserId( $userId );
            }
            else {
                $this->Data->add_error('user_not_exists', 'admin');
            }
            // если есть изменения в правах
            if ( $this->rules_changed() && $RulesManager->rules_changed() )
            {
                if ( $RulesManager->update_rules() ) {
                    throw new Redirect( $this->Ways->add('mess', 'changes_updated' ) );
                }
                else {
                    $this->Data->add_error( $RulesManager->get_errors(), 'admin' );
                }
            }
        }

        $this->Data->set_rules_list( $RulesManager->get_rules_list() );
    }

    private function show()
    {
        return $this->getWayPattern(0) > 0;
    }

    private function userId()
    {
        return (int) $this->getWayPattern(0);
    }

    private function rules_changed()
    {
         return $this->Routing->route_post('rules_changed') !== null && $this->show();
    }
}

class UsersManager {

    private $users;

    public function is_user_exist_with_id( $id_user ) {

        if ( isset( $this->users[$id_user] ) )
            return true;

        return $this->get_user_info( $id_user );
    }

    public function get_username( $id_user ) {

        if ( isset( $this->users[$id_user] ) ) {
            return $this->users[$id_user]['username'];
        } else {
             if ( $this->get_user_info( $id_user ) )
                return $this->users[$id_user]['username'];
        }
    }

    private function get_user_info( $id_user ) {

        $statement = \Orgup\Application\Registry::instance()->get('db')->executeQuery(
            'SELECT * FROM `users` WHERE `id_user` = ?',
            array( $id_user ),
            array( \PDO::PARAM_INT )
        );

        $result = $statement->fetch();

        if ( $result ) {
            $this->users[$id_user] = $result;
            return true;
        }

        return false;
    }
}