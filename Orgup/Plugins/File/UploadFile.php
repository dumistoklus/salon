<?php

namespace Orgup\Plugins\File;

class UploadFile {

    protected $file = array( 'name' => null );

    protected $perExtension = array();
    protected $error = array();

    private $path = '';
    private $maxSize;
    private $file_name;
    private $fullName;
    private $moved = false;

    public function __construct( $file, $path, $maxSize = 6291456, $name = null ) {

        $this->maxSize = $maxSize;
        $this->file = $file;
        $this->path = $path;
        $this->file_name = $name;
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function setName( $filename ) {
        $this->file_name = $filename;
    }

    public function getOldName() {
        return $this->file['name'];
    }

    public function get_md5() {
        return md5_file( $this->moved ? $this->path . basename( $this->fullName ) : $this->file['tmp_name'] );
    }

    public function upload() {

        if ( $this->file_name !== null ) {
            $this->fullName = $this->file_name.'.'.$this->file['ext'];
        } else {
            $this->fullName = 'img'.time().'.'.$this->file['ext'];
        }

        $file_move_to = $this->path . basename( $this->fullName );

        if ( !move_uploaded_file( $this->file['tmp_name'], $file_move_to ) ) {
            $this->error[] = 'file_not_moved_to_farm';
            return false;
        }

        return true;
    }

    public function getErrors() {
        return $this->error;
    }

    public function checkFile() {
        
        if ( !is_array( $this->file ) || !isset( $this->file['size'], $this->file['name'], $this->file['tmp_name'] ) ) {
            $this->error[] = 'invalid_file_uploading';
            return false;
        }

        $this->file['size'] = filesize( $this->file['tmp_name'] );
        if ($this->file['size'] > $this->maxSize) {
            $this->error[] = 'exceeded_maximum_file_upload_size';
            return false;
        }

        $expExtansion = explode('.', $this->file['name'] );
        $this->file['ext'] = mb_strtolower( $expExtansion[ (count($expExtansion) - 1) ] );

        if ( !isset( $this->file['name'], $this->file['tmp_name'], $this->file['size'], $this->file['ext'] ) ) {
            $this->error[] = 'invalid_file_extension';
            return false;
        }

        if (!in_array( $this->file['ext'], $this->perExtension ) ) {
            $this->error[] = 'invalid_file_extension';
            return false;
        }

        if (!is_uploaded_file( $this->file['tmp_name'] ) ) {
           $this->error[] = 'file_not_uploaded "'.$this->file['tmp_name'].'"';
           return false;
        }

        return true;
    }
}