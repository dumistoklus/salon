<?php

namespace Orgup\Modules\Index\Administration;

use Orgup\Application\Registry;

class Upload extends AdministrationModuleBuilder {

    public function run() {

        if ( $this->Routing->route_files('upload') ) {

            $folder = '/images/';

            $UploadImage = new UploadImage( $this->Routing->route_files('upload'), ROOTDIR.'htdocs'.$folder );
            $UploadImage->upload();
            $this->Data->setName( $folder.$UploadImage->getName() );
            $this->Data->add_error( $UploadImage->getErrors(), 'admin' );
        }
    }
}

class UploadImage {

    private $file = array( 'name' => null );
    private $path = '';
    private $perExtension = array('jpeg', 'jpg', 'gif', 'png');
    private $error = array();
    private $maxSize;

    public function __construct( $file, $path, $maxSize = 6291456 ) {
        $this->maxSize = $maxSize;
        $this->file = $file;
        $this->path = $path;
    }

    public function getName() {
        return $this->file['name'];
    }

    public function upload() {

        if( !$this->checkFile() )
            return false;

        $this->file['name'] = 'img'.time().'.'.$this->file['ext'];

        $temp_file = $this->file['tmp_name'];

        // проверяем полностью ли залился файл
        if (!is_uploaded_file( $temp_file ) ) {
           $this->error[] = 'file_not_uploaded';
           return false;
        }

        // убеждаемся, что файл - изображение
        if ( !@getimagesize( $temp_file ) ) {
            $this->error[] = 'invalid_file_extension';
            return false;
        }

        $file_move_to = $this->path . basename( $this->file['name'] );

        // файл удалось перенести в папку назначени
        if ( !move_uploaded_file( $temp_file, $file_move_to ) ) {
            $this->error[] = 'file_not_moved_to_farm';
            return false;
        }

        return true;
    }

    public function getErrors() {
        return $this->error;
    }

    private function checkFile() {

        // проверяем, что данные о файле - массив
        if ( !is_array( $this->file ) ) {
            $this->error[] = 'invalid_file_extension';
            return false;
        }

        // проверяем размер файла
        $this->file['size'] = filesize( $this->file['tmp_name'] );
        if ($this->file['size'] > $this->maxSize) {
            $this->error[] = 'exceeded_maximum_file_upload_size';
            return false;
        }

        // проверяем расширение файла
        $expExtansion = explode('.', $this->file['name'] );
        $this->file['ext'] = mb_strtolower( $expExtansion[ (count($expExtansion) - 1) ] );

        // проверяем, что не был передан массив файлов
        if ( !isset( $this->file['name'], $this->file['tmp_name'], $this->file['size'], $this->file['ext'] ) ) {
            $this->error[] = 'invalid_file_extension';
            return false;
        }

        if (!in_array( $this->file['ext'], $this->perExtension ) ) {
            $this->error[] = 'invalid_file_extension';
            return false;
        }

        return true;
    }
}