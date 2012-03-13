<?php
namespace Orgup\Common\Administration;
use \Orgup\Application\Registry;
use \Orgup\Application\Rights;

class RulesManager {

    private $errors = array();
    private $is_user_rules;
    private $id;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $DB;

    private $system_rules;

    private $rules_list;
    private $new_rules_status = array();
    private $table_name;
    private $column_name;

    private $rules;

    private $rulesChanged = false;


    public function __construct($user_id, $addRules) {

        $this->set_user_id($user_id);
        $this->rules = $addRules;

        $this->DB = Registry::instance()->get('db');

        $this->table_name = 'users_rights';
        $this->column_name = 'id_user';

        $this->system_rules = Rights::instatnce()->getRights();

        $this->rulesChanged = $this->check_rules_changes();
    }

    public function rules_changed()
    {
        return $this->rulesChanged;
    }

    private function check_rules_changes()
    {
        foreach ( $this->system_rules as $rule_name ) {
            $this->new_rules_status[$rule_name] = $this->is_rule_changed($rule_name);
        }

        if (! empty( $this->new_rules_status) ) return true;

        return false;
    }

    private function is_rule_changed($rule_name)
    {
        return isset($this->rules[$rule_name]) && $this->rules[$rule_name] !== null;
    }

    public function get_errors() {
        return $this->errors;
    }

    public function update_rules() {

        if ( !$this->rules_list )
            $this->get_rules_list_from_bd();

        $changes = $this->get_changes();

        if ( $changes ) {
            $this->delete_rights( $changes['delete'] );
            $this->insert_rights( $changes['insert'] );
            return true;
        }
        else {
            $this->errors[] = 'rules_not_updated';
            return false;
        }
    }

    private function get_changes() {

        $rules_to_insert = array();
        $rules_to_delete = array();

        $changed_status = true;

        foreach ( $this->rules_list as $rule_name => $rule_status ) {

            if ( $rule_status != $this->new_rules_status[$rule_name] ) {

                if ( $rule_status === $changed_status ) {
                    $rules_to_delete[] = $rule_name;
                }
                else {
                    $rules_to_insert[] = $rule_name;
                }
            }
        }

        if ( $rules_to_delete || $rules_to_insert ) {
            return array( 'delete' => $rules_to_delete, 'insert' => $rules_to_insert );
        }

        return false;
    }

    public function is_user_rules( $is_user_rules ) {
        $this->is_user_rules = $is_user_rules;
    }

    public function get_rules_list() {

        if ( !$this->rules_list )
            $this->get_rules_list_from_bd();

        return $this->rules_list;
    }

    private function set_user_id( $id_user ) {
        $this->is_user_rules = true;
        $this->id = $id_user;
    }

    private function get_rules_list_from_bd() {

        $sql = 'SELECT `right` FROM `'.$this->table_name.'` WHERE `'.$this->column_name.'` = ?';

        $result = $this->DB->executeQuery(
            $sql,
            array( $this->id ),
            array( \PDO::PARAM_INT )
        )->fetchAll(\PDO::FETCH_ASSOC);



        foreach ( $result as $row ) {
            if ( in_array( $row['right'], $this->system_rules ) )
                $this->rules_list[$row['right']] = true;
        }

        foreach (  $this->system_rules as $rule ) {
            if ( !isset( $this->rules_list[$rule] ) )
                $this->rules_list[$rule] = false;
        }

        ksort( $this->rules_list );

    }

    private function delete_rights( $deleted_rights ) {

        if (!$deleted_rights)
            return false;

        $sql_extend = implode( ' OR ', array_fill( 0, count( $deleted_rights), '`right` = ?' ) );
        $sql = 'DELETE FROM `'.$this->table_name.'` WHERE `'.$this->column_name.'` = ? AND ('.$sql_extend.')';

        array_unshift( $deleted_rights, $this->id );

        $this->DB->executeQuery($sql, $deleted_rights);
        return true;
    }

    private function insert_rights( $inserted_rights ) {

        if ( !$inserted_rights )
            return false;

        $sql_extend = implode( '),(', array_fill( 0, count( $inserted_rights ), $this->id.", ?" ) );
        $sql = 'INSERT INTO `'.$this->table_name.'` (`'.$this->column_name.'`,`right`) VALUES ('.$sql_extend.')';

        $this->DB->executeQuery($sql, $inserted_rights);
        return true;
    }
}