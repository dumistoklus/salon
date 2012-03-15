<?php

namespace Orgup\DataModels\Index;
use \Orgup\DataModels\IndexData;

class Search extends IndexData {

    private $_fabriki = array();
    private $_strani = array();
    private $_query = array();
    private $_strana;
    private $_results;

    public function initStylesAndScripts() {
        $this->add_style('search');
    }

    public function fabriki() {

        $this->_fabriki[0] = 'любая';

        return $this->_fabriki;
    }

    public function setFabriki( $fabriki ) {
        $this->_fabriki = $fabriki;
    }
    
    public function setStrani($strani) {
        $this->_strani = $strani;
    }

    public function setQuery($query)
    {
        $this->_query = $query;
    }

    public function getQuery()
    {
        return $this->_query;
    }

    public function setStrana($strana)
    {
        $this->_strana = $strana;
    }

    public function strani()
    {
        $this->_strani[0] = 'любая';
        return $this->_strani;
    }

    public function setResults($results)
    {
        $this->_results = $results;
    }

    public function getResults()
    {
        return $this->_results;
    }
}