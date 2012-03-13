<?php

namespace Orgup\DataModels\Index\Administration;
use \Orgup\DataModels\IndexData;

class Site extends Administration {

    private $stylesLastUpdateDate;
    private $scriptsLastUpdateDate;

    public function setStylesLastUpdateDate($stylesLastUpdateDate) {
        $this->stylesLastUpdateDate = $stylesLastUpdateDate;
    }

    public function setScriptsLastUpdateDate($scriptsLastUpdateDate) {
        $this->scriptsLastUpdateDate = $scriptsLastUpdateDate;
    }

    public function getStylesLastUpdateDate() {
        return $this->stylesLastUpdateDate;
    }

    public function getScriptsLastUpdateDate() {
        return $this->scriptsLastUpdateDate;
    }
}