<?php

namespace Orgup\DataModels\Index;
use \Orgup\DataModels\IndexData;

class Search extends IndexData {

    private $_fabriki = array();
    private $_cats = array();
    private $_query = array();
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
    
    public function setStrani($strani) {}

    public function setQuery($query)
    {
        $this->_query = $query;
    }

    public function getQuery()
    {
        return $this->_query;
    }

    public function setStrana($strana)
    {}


    public function setResults($results)
    {
        $this->_results = $results;
    }

    public function getResults()
    {
        return $this->_results;
    }

    public function setCats($cats)
    {
        $this->_cats = $cats;
    }

    public function getCats()
    {
        return $this->_cats;
    }
}