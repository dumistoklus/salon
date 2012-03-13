<?php
namespace Orgup\DataModels\Index\Administration\Users;
use \Orgup\DataModels\Index\Administration\Administration;
use \Orgup\Common\Mod;

class NewUser extends Administration
{
    /**
     * @var \Orgup\DataModels\Index\Administration\UserData
     */
    private $UserData;

    function __construct()
    {
        parent::__construct();

        $this->UserData = new UserData();

        if( ! $this->noErrors() && $this->isEditing())
        {
            $this->add_form_error_by_field_name($this->UserData->get_errors('Validate'), 'admin');
        }
    }

    public function noErrors()
    {
        return $this->UserData->noError();
    }

    public function isEditing()
    {
        return $this->UserData->isEditing();
    }

    public function getName()
    {
        return $this->UserData->name;
    }

    public function getPassword()
    {
        return $this->UserData->password;
    }

    public function getRePassword()
    {
        return $this->UserData->re_password;
    }

	public function getEmail() {
		return $this->UserData->email;
	}

	public function getSendEmail() {
		return $this->UserData->sendRegInfo();
	}

    public function clear()
    {
        $this->UserData->name = '';
        $this->UserData->password = '';
        $this->UserData->re_password = '';
    }
}

class UserData extends Mod
{
    /**
     * @Post()
     * @Trim()
     * @Validate(type="login")
     */
    public $name;

    /**
     * @Post()
     * @Validate(type="password")
     */
    public $password;

    /**
     * @Post()
     * @Validate(type="equals", between="{{password}}")
     */
    public $re_password;

    /**
     * @Post()
     */
    private $editing;
    /**
     * @Post()
     * @Validate(type="email")
     */
    public $email;

    /**
     * @Post(is_checkbox="true")
     */
    private $send_email;


    function __construct()
    {
        parent::__construct();
        $this->initModifiers();
    }

    public function isEditing()
    {
        return $this->editing == 1;
    }

    public function sendRegInfo()
    {
        return $this->send_email;
    }
}