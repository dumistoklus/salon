<?php
namespace Orgup\DataModels\Index\Administration;

class Fabriki extends Administration {

    private $fabriki;
    private $name;
    private $fabrika_id;

    public function setFabriki($fabriki)
    {
        $this->fabriki = $fabriki;
    }

    public function getFabriki()
    {
        return $this->fabriki;
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