<?php

namespace Orgup\Modules\Index;

use \Orgup\Modules\IndexModuleBuilder;
use \Orgup\Application\Registry;
use Orgup\Common\DBAccess\DBAccess;

class Search extends IndexModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Search
     */
    protected $Data;
    private $fabriki = array();
    private $country = array();
    const GOODS_PER_PAGE = 20;

    public function run() {
        $current_page = ($this->Routing->route_get('p') > 0) ? $this->Routing->route_get('p') : 1;       
        $Searching = new Searching(
                $current_page * self::GOODS_PER_PAGE - self::GOODS_PER_PAGE,
                self::GOODS_PER_PAGE,
                $this->Routing->route_get('q'), 
                $this->Routing->route_get('strana'), 
                $this->Routing->route_get('fabrika')
                );
        $this->Data->setResults($Searching->results());
        $query = array(
            'q'         => $this->Routing->route_get('q'),
            'strana'    => $this->Routing->route_get('strana'),
            'fabrika'   => $this->Routing->route_get('fabrika')
        );
        
        $this->Data->add_paginator( $Searching->count(), $current_page, self::GOODS_PER_PAGE );
        $this->Data->setQuery($query);
        $this->initFabriki();
        $this->initCountry();
        $this->Data->setStrani($this->country);
        $this->Data->setFabriki($this->fabriki);
    }

    private function initFabriki() {
        $query = $this->getDB()->query('SELECT  * FROM fabriki');
        foreach ($query as $row) {
            $this->fabriki[$row['fabrika_id']] = $row['name'];
        }
    }

    private function initCountry() {
        $query = $this->getDB()->query('SELECT  * FROM country');
        foreach ($query as $row) {
            $this->country[$row['country_id']] = $row['name'];
        }
    }

}

class Searching extends DBAccess {

    private $string;
    private $fabrika_id;
    private $country_id;
    private $where = '';
    private $results = array();
    private $count = 0;

    public function __construct($limit, $start, $string, $country_id, $fabrika_id) {
        $this->limit = (int)$limit;
        $this->start = (int)$start;

        $this->string = trim($string);
        $this->fabrika_id = (int) $fabrika_id;
        $this->country_id = (int) $country_id;
        $this->setWhere();
        $this->initResults();
        $this->initCount();
    }

    public function results() {
        return $this->results;
    }
    
    public function count() {
        return $this->count;
    }

    private function initResults() {
        $sql = 'SELECT goods.*, fab.name as fabrika_name, fab.`fabrika_id`, ct.name as country FROM goods as goods
        LEFT JOIN `fabriki` AS fab ON goods.fabrika_id = fab.fabrika_id
        LEFT JOIN `country` AS ct ON goods.country_id = ct.country_id 
        ' . $this->where. ' LIMIT '.$this->limit.' , '.$this->start;
        $stmt = $this->getDB()->prepare($sql);
        if ($this->string != '') {
            $stmt->bindValue("querystring", '%'.$this->string.'%');
        }
        $stmt->execute();
        $this->results = $stmt->fetchAll();
    }
    
    private function initCount() {
        $sql = 'SELECT COUNT(*) as c FROM goods as goods
        LEFT JOIN `fabriki` AS fab ON goods.fabrika_id = fab.fabrika_id
        LEFT JOIN `country` AS ct ON goods.country_id = ct.country_id 
        ' . $this->where;
        $stmt = $this->getDB()->prepare($sql);
        if ($this->string != '') {
            $stmt->bindValue("querystring", '%'.$this->string.'%');
        }
        $stmt->execute();
        $result = $stmt->fetchAll();
        if(isset($result[0]['c']))
            $this->count = $result[0]['c'];
    }

    private function setWhere() {
        $where = array();
        if ($this->fabrika_id)
            $where[] = 'goods.fabrika_id = ' . $this->fabrika_id;
        if ($this->country_id)
            $where[] = 'goods.country_id = ' . $this->country_id;
        if ($this->string != '') {
            $where[] = ' (goods.name LIKE :querystring OR goods.description LIKE :querystring)';
        }
        if (count($where) > 0) {
            $this->where .= ' WHERE ' . implode(' AND ', $where);
        }
    }

}