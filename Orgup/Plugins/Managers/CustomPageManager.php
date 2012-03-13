<?php

namespace Orgup\Plugins\Managers;
use \Orgup\Application\Registry;

interface PageManagerInterface {

    public function __construct( $db, \Orgup\Application\Routing $Routing );
    public function check_changes();
    public function ready_to_create();
    public function update();
    public function create();
    public function delete();
    public function get_errors();
    public function get_PageModel();
    public function get_new_id();
    public function set_page_id( $id );
}

class CustomPageManager implements PageManagerInterface {

    private $PageModel;
    private $Routing;
    private $page_id;
    private $new_page_id;

    private $fields = array(
        'name',
        'content',
        'title',
        'keywords',
        'description'
    );

    private $errors = array();
    private $db;

    public function __construct( $db, \Orgup\Application\Routing $Routing ) {
        $this->PageModel = new EmptyPage();
        $this->Routing = $Routing;
        $this->db = $db;
    }

    public function check_changes() {
        return (bool)$this->Routing->route_post('editpage') && !is_null( $this->page_id );
    }

    public function ready_to_create () {
        return (bool)$this->Routing->route_post('editpage');
    }

    public function update() {
        $this->route_fields();
        if ( $this->check_fields() ) {
            return $this->update_in_db();
        }

        return false;
    }

    public function delete() {

        if ( is_null( $this->page_id ) ) {
            $this->errors[] = 'page_already_deleted';
            return false;
        }

        $result = $this->db->delete('custom_pages', array( 'id_page' => $this->page_id ) );
        if ( !$result ) {
            $this->errors[] = 'page_not_deleted';
        }
        return (bool)$result;
    }

    public function create() {

        $this->route_fields();
        if ( $this->check_fields() ) {
            return $this->insert_in_db();
        }

        return false;
    }

    public function get_errors() {
        return $this->errors;
    }

    public function set_page_id( $id ) {

        $id = (int)$id;

        if ( $id > 0 ) {

            $result = $this->db->fetchAssoc('SELECT * FROM custom_pages WHERE `id_page` = ?', array( $id ) );

            if ( $result ) {

                foreach ( $this->fields as $field )
                    $this->PageModel->$field = $result[$field];

                $this->page_id = $id;
                return true;
            }
        }

        return false;
    }

    public function get_new_id() {
        return $this->new_page_id;
    }

    public function get_PageModel() {
        return $this->PageModel;
    }

    private function check_fields() {

        $errors = array();

        if ( $this->name_is_short() ) {
            $errors[] = 'name_in_short';
        }

        if ( empty( $errors ) ) {
            return true;
        } else {
            $this->errors += $errors;
            return false;
        }
    }

    private function route_fields() {

        foreach ( $this->fields as $field )
                    $this->PageModel->$field = trim( (string)$this->Routing->route_post($field) );
    }

    private function name_is_short() {
        return ( mb_strlen( $this->PageModel->name ) < 1 );
    }

    private function update_in_db() {

        $upd = array();
        foreach ( $this->fields as $field_name ) {
            $upd[] = '`'.$field_name.'` = :'.$field_name;
        }

        $stmt = $this->db->prepare('UPDATE custom_pages SET '.implode(', ', $upd ).' WHERE `id_page` = :id_page');

        // prepare fields
        foreach ( $this->fields as $field_name ) {
            $stmt->bindValue($field_name, $this->PageModel->$field_name, \PDO::PARAM_STR );
        }
        $stmt->bindValue('id_page', $this->page_id );

        $result = $stmt->execute();

        if ( $result ) {
            return true;
        }

        $this->errors[] = 'error_with_update';
        return false;
    }

    private function insert_in_db() {

        $stmt = $this->db->prepare("INSERT INTO custom_pages (`name`, `content`, `title`, `keywords`, `description`, `id_author`, `id_last_editor`)
        VALUES (:name, :content, :title, :keywords, :description, :id_author, :id_last_editor )");

        $user_id = Registry::instance()->User()->get('id_user');

        foreach ( $this->fields as $field_name ) {
            $stmt->bindParam(":".$field_name, $this->PageModel->$field_name, \PDO::PARAM_STR );
        }

        $stmt->bindParam(":id_author", $user_id, \PDO::PARAM_INT );
        $stmt->bindParam(":id_last_editor", $user_id, \PDO::PARAM_INT );

        if ( $stmt->execute() )
            return $this->new_page_id = $this->db->lastInsertId();
        return false;
    }
}

class EmptyPage {
    public $name = '';
    public $content = '';
    public $title = '';
    public $keywords = '';
    public $description = '';
}