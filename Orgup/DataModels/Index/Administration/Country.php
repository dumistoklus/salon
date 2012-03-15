<?php
namespace Orgup\DataModels\Index\Administration;

class Country extends Administration {

    private $country;
    private $name;
    private $fabrika_id;

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setFabrikaId($fabrika_id)
    {
        $this->fabrika_id = $fabrika_id;
    }

    public function getFabrikaId()
    {
        return $this->fabrika_id;
    }
}