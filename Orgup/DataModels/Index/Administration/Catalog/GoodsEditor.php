<?php

namespace Orgup\DataModels\Index\Administration\Catalog;
use \Orgup\DataModels\Index\Administration\Administration;
use \Orgup\Plugins\Managers\CatalogManager;

class GoodsEditor extends Administration {

    private $_isCreating = true;

    /**
     * @var Goods
     */
    private $Goods;

    /**
     * @var \Orgup\Plugins\Managers\CatalogManager
     */
    private $CatsManager;

    /**
     * @var \Orgup\Plugins\Managers\FabrikiManager
     */
    private $FabManager;
    
    /**
     * @var \Orgup\Plugins\Managers\CountryManager
     */
    private $CountryManager;

    private $_data_obtained = false;
    private $_data_no_errors = false;
    private $_images = array();
    private $_id;

    public function initGoods() {

        $this->Goods = new Goods();
        $errors = $this->Goods->get_errors('Post');

        if ( empty( $errors ) ) {

            $this->data_obtained();

            if ( !$this->Goods->noError() ) {

                $this->add_error( $this->Goods->validateErrors(), 'goods' );

            } else {

                $this->_data_no_errors = true;

                $cats = $this->cats();
                if ( !isset( $cats[$this->Goods->cat_id]) ) {
                    $this->add_error( 'description', 'goods' );
                }
            }
        }
    }

    public function uploadPhoto( $id ) {

        $Routing = \Orgup\Application\Registry::instance()->get('Routing');
        $db = \Orgup\Application\Registry::instance()->get('db');

        $images = $Routing->route_files('image');

        if ( empty( $images ) || !isset( $images['name'] ) || count( $images['name'] ) == 0 )
            return false;

        $path_to_originals = ROOTDIR.'htdocs/images/o/';
        $path_to_big       = ROOTDIR.'htdocs/images/b/';
        $path_to_middle    = ROOTDIR.'htdocs/images/m/';
        $path_to_small     = ROOTDIR.'htdocs/images/s/';

        $new_images = array();

        foreach( $images['name'] as $key => $name ) {
            $new_images[$key] =array(
                'name'     => $name,
                'type'     => $images['type'][$key],
                'tmp_name' => $images['tmp_name'][$key],
                'error'    => $images['error'][$key],
                'size'     => $images['size'][$key]
            );
        }

        foreach ( $new_images as $image ) {

            $UploadImage = new \Orgup\Plugins\File\UploadImage( $image, $path_to_originals );

            if ( $UploadImage->checkFile() ) {

                $db->insert('images', array( 'id' => $id, 'ext' => $UploadImage->getExtension() ) );
                $image_id = $db->lastInsertId();

                $UploadImage->setName( $image_id );

                if ( $UploadImage->upload() ) {

                    $ResizeImage = new \Orgup\Plugins\File\ResizeImage();
                    $ResizeImage->resizeimg( $path_to_originals.$UploadImage->getFullName(), $path_to_big.$UploadImage->getFullName(), 400, 400 );
                    $ResizeImage->resizeimg( $path_to_originals.$UploadImage->getFullName(), $path_to_middle.$UploadImage->getFullName(), 200, 200,true );
                    $ResizeImage->resizeimg( $path_to_middle.$UploadImage->getFullName(), $path_to_small.$UploadImage->getFullName(), 100, 100, true );
                }
            }
        }

        return true;
    }

    public function delete_photo() {

        $Routing = \Orgup\Application\Registry::instance()->get('Routing');
        $db = \Orgup\Application\Registry::instance()->get('db');

        $image_id = (int)$Routing->route_get('remove_image');

        if ( $image_id > 0 ) {
            $this->add_notification('Фото удалено!', 'goods');
            return $db->delete('images', array( 'image_id' => $image_id ) );
        }

        return false;
    }

    public function setImages( $images ) {

        if ( is_array( $images ) ) {
            foreach ( $images as $image ) {
                $this->_images[] = array( 'file' => $image['image_id'].'.'.$image['ext'], 'id' => $image['image_id'] );
            }
        }
    }

    public function images() {
        return $this->_images;
    }

    public function getMiddleImagePath( $file ) {
        return '/images/b/'.$file;
    }

    public function getSmallImagePath( $file ) {
        return '/images/s/'.$file;
    }

    public function initStylesAndScripts() {

        $this->add_style('goods_editor');
        $this->add_script('goods_editor');
        $this->run_script('goods_editor');
    }

    public function data_obtained() {
        $this->_data_obtained = true;
    }

    public function dataIsObtained() {
        return $this->_data_obtained;
    }

    public function isDataNotHaveErrors() {
        return $this->_data_no_errors;
    }

    public function isCreating() {
        return $this->_isCreating;
    }

    public function thisIsEditing() {
        $this->_isCreating = false;
    }

    public function cats() {

        if ( !$this->CatsManager ) {
            $this->CatsManager = new CatalogManager();
        }

        return $this->CatsManager->getCats();
    }

    public function fabriki() {

        if ( !$this->FabManager ) {
            $this->FabManager = new \Orgup\Plugins\Managers\FabrikiManager();
        }

        return $this->FabManager->getFabriki();
    }
    
    public function country() {

        if ( !$this->CountryManager ) {
            $this->CountryManager = new \Orgup\Plugins\Managers\CountryManager();
        }

        return $this->CountryManager->getCountry();
    }

    public function goods() {
        if ( is_object( $this->Goods ) ) {
            return $this->Goods->originalsArray();
        }
        else
            return $this->Goods;
    }

    public function getObtainedGoods() {
        if ( is_object( $this->Goods ) ) {
            return $this->Goods;
        }
    }

    public function setGoods( $goods ) {
        $this->Goods = $goods;
    }

    public function getId() {
        return $this->_id;
    }

    public function setId( $id ) {
        $this->_id = $id;
    }
}

class Goods extends \Orgup\Common\Mod {

    public function __construct() {
        $this->initModifiers();
    }

    /**
     * @Post()
     * @Trim()
     * @Validate(type="numeric")
     */
    public $cat_id = 0;

    /**
     * @Post()
     * @Trim()
     * @Validate(type="numeric")
     */
    public $fabrika_id = 1;
    
    /**
     * @Post()
     * @Trim()
     * @Validate(type="numeric")
     */
    public $country_id;

    /**
     * @Post()
     * @Trim()
     * @Validate(type="length",between="2|inf")
     */
    public $name = '';

    /**
     * @Post()
     * @Trim()
     * @Validate(type="numericOrVoid")
     */
    public $price = '';

    /**
     * @Post()
     * @Trim()
     */
    public $description = '';
}