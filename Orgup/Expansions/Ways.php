<?php

namespace Orgup\Expansions;
use Orgup\Application\Registry;
use \Orgup\Application\Logger;

interface WaysInterface {
    public function add( $param, $value, $path = null );
    public function delete_param( $param, $path = null );
    public function thispage();
    /** mainpage. example http://localhost/ */
    public function path();
    public function adminpage();
    /**
     * @return string path/?logout=1
     */
    public function logout();
}

class Ways implements WaysInterface {

    private $host;

    public function __construct() {
        Logger::log('Init Ways', __FILE__, __LINE__);
        $this->host = Registry::instance()->get('HOST');
    }

    public function path() {
        return $this->host;
    }

    public function domain()
    {
        return $_SERVER['HTTP_HOST'];
    }

    public function thispage() {
        return Registry::instance()->get('Path')->get_full_path();
    }

    public function logout() {
        return $this->add( 'logout', 1 );
    }

    public function add( $param, $value, $path = null ) {
        return Registry::instance()->get('Path')->add_param( $param, $value, $path );
    }

    public function delete_param( $param, $path = null  ) {
        return Registry::instance()->get('Path')->delete_param( $param, $path );
    }

    public function adminpage() {
        return $this->host.'administration/';
    }

    /**
     * @return string path?entry=1
     */
    public function entry() {
        return $this->add( 'entry', 1 );
    }

    public function login() {
        return $this->host.'login/';
    }

    public function catalog( $id ) {
        return $this->host.'cat'.$id;
    }

    public function goods( $id ) {
        return $this->host.'g'.$id;
    }

    // адрес определенной страницы
    public function custom_page_id( $id_page ) {
        return $this->host.'page/'.$id_page;
    }

    // ##################################
    //            ADMIN METHODS
    // ##################################
    public function get_admin_menu_massive() {
        return array(
            'site' => array(
                'pages'                 => $this->admin_custom_pages()
            ),
            'users' => array(
                'users'                 => $this->admin_users_list()
            ),
            'site_manager' => array(
                'site_manager'          => $this->admin_site()
            ),
            'catalog' => array(
                'catalog'               => $this->admin_catalog(),
                'goods'                 => $this->admin_goods(),
                'fabriki'               => $this->admin_fabriki()
            )
        );
    }

	// загрузчик изображений
    public function admin_upload_image() {
        return $this->adminpage().'upload/';
    }

    // подгрузка ckfinder
    public function admin_ckfinder() {
        return $this->adminpage().'ckfinder/?Type=Images';
    }

    public function admin_ckfinder_connector() {
        return $this->adminpage().'connector/';
    }

	#################### CUSTOM PAGE EDITOR ####################
	// список страниц
    public function admin_custom_pages() {
        return $this->adminpage().'pages/';
    }

	// удаление страницы
    public function admin_custom_page_delete( $id ) {
        return $this->add( 'delete_page', $id, $this->admin_custom_pages() );
    }

	// редактирование страницы
    public function admin_custom_page( $id ) {
        return $this->admin_custom_pages().$id;
    }

	// новая страница
    public function admin_new_page() {
        return $this->admin_custom_pages().'newpage';
    }
	#################### / CUSTOM PAGE EDITOR ####################

    public function admin_user_to_ban( $id ) {
        return $this->add( 'user_to_ban', $id, $this->admin_users_list() );
    }

    public function admin_user_to_unban( $id ) {
        return $this->add( 'user_to_unban', $id, $this->admin_users_list() );
    }

    public function admin_user_rules( $id ) {
        return $this->admin_user( $id ).'rules/';
    }

    public function admin_users_list() {
        return $this->adminpage().'users/';
    }

    public function admin_user_new()
    {
        return $this->admin_users_list().'new/';
    }

    public function admin_user( $user_id ) {
        return $this->admin_users_list().$user_id.'/';
    }

    ######################### Управление сайтом #########################

    public function admin_site() {
        return $this->adminpage().'site/';
    }

    public function admin_catalog() {
        return $this->adminpage().'catalog/';
    }

    public function admin_remove_catalog( $id ) {
        return $this->add( 'remove', $id,  $this->admin_catalog() );
    }

    public function admin_edit_catalog( $id ) {
        return $this->add( 'edit', $id,  $this->admin_catalog() );
    }

    public function admin_goods() {
        return $this->adminpage().'goods/';
    }

    public function admin_new_goods() {
        return $this->admin_goods().'new/';
    }

    public function admin_goods_edit( $id ) {
        return $this->admin_goods().$id;
    }

    public function admin_delete_image( $goods_id, $image_id ) {
        return $this->add( 'remove_image', $image_id, $this->admin_goods_edit( $goods_id ) );
    }

    public function admin_remove_goods( $id ) {
        return $this->add( 'remove_goods', $id );
    }

    public function admin_fabriki( $id = null ) {

        $path = $this->adminpage().'fabriki/';

        if ( $id )
            $path = $this->add( 'id', $id, $path );

        return $path;
    }

    public function admin_fabriki_delete( $id ) {
        return $this->add( 'delete', $id, $this->admin_fabriki() );
    }
}