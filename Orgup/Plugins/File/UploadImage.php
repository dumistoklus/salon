<?php

namespace Orgup\Plugins\File;

class UploadImage extends UploadFile {

    protected $perExtension = array('jpeg', 'jpg', 'gif', 'png');

    public function checkFile() {

        if ( parent::checkFile() ) {

            if ( !@getimagesize( $this->file['tmp_name'] ) ) {
                $this->error[] = 'invalid_file_uploading';
                return false;
            }

            return true;
        }
        
        return false;
    }

    public function getExtension() {
        return $this->file['ext'];
    }
}