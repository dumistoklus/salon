<?php

namespace Orgup\DataModels\Index\Administration\Users;
use \Orgup\DataModels\Index\Administration\Administration;
use \Orgup\Common\Mod;

class Users extends Administration {

    private $Users = array();
    private $_cityList = array();
    private $_query = array();
    /**
     * @var \Orgup\DataModels\Index\Administration\Users\Filter
     */
    private $Filter;

    function __construct()
    {
        parent::__construct();

        $this->Filter = new Filter();
        $this->setQuery($this->Filter->originalsArray());
    }

    public function initStylesAndScripts()
    {
        $this->add_style('users');
    }
    /**
     * @return Filter
     */
    public function filter()
    {
        return $this->Filter;
    }

    public function setUsers( array $Users ) {
        $this->Users = $Users;
    }

    public function getUsers() {
        return $this->Users;
    }

    public function setCityList( $cityList ) {
        $this->_cityList = $cityList;
    }

    public function query() {
        return $this->_query;
    }

    public function setQuery( $query ) {
        $this->_query = $query;
    }
}


class Filter extends Mod
{
    /**
     * @Post()
     * @Trim()
     */
    private $username = '';
    /**
     * @Post()
     * @Validate(type="numeric", between="0|inf")
     */
    private $city_id;
    /**
     * @Get()
     * @Trim()
     * @Validate(type="numeric", between="1|inf")
     */
    private $user_to_ban;
    /**
     * @Get(name="p", suppress_warnings="true")
     */
    private $page;


    function __construct()
    {
        parent::__construct();

        $this->initModifiers();
    }

    public function userName()
    {
        return $this->username;
    }

    public function cityId()
    {
        return (int) $this->city_id;
    }

    public function page() {
        if($this->isSearch()) {
            return 1;
        } else {
            return is_numeric($this->page) && $this->page > 0 ? $this->page : 1;
        }
    }

    public function userForBan()
    {
        return (int) $this->user_to_ban;
    }

    public function isSearch()
    {
        $postErrors = $this->get_errors('Post');

        return empty($postErrors) && !in_array('city_id', $this->validateErrors());
    }


    public function isUserBan()
    {
        $getErrors = $this->get_errors('Get');

        return empty($getErrors) && !in_array('user_to_ban', $this->validateErrors());
    }
}