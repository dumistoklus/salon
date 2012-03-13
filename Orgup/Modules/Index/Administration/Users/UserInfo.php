<?php
namespace Orgup\Modules\Index\Administration\Users;
use \Orgup\Modules\Index\Administration\AdministrationModuleBuilder;
use \Orgup\Common\DBAccess\ActiveRecord;
use \Orgup\Common\Mod;
use \Orgup\Common\Hash;
use \Orgup\Plugins\SendMail;
use \Orgup\Plugins\RequestManager\Mail;
use Orgup\Application\Exception\Module\E404;

class UserInfo extends AdministrationModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Administration\Users\UserInfo
     */
    protected $Data;

    public function run()
    {
        $User = new User($this->getWayPattern(0));

        $info = $User->asArray();

        if(!empty($info))
        {
            $info = $info[0];
            $email = ($info['city_user'] == '1') ? $info['email'] : $info['user_email'];
            $info['email'] = $email;
            $this->Data->setUser($info);
        }
        else
        {
            throw new E404();
        }

        $UserData = new UserData();

        if($UserData->passwordChange())
        {
            if($UserData->canChangePassword())
            {
                $result = $this->getDB()->update('users', array('password' => Hash::password($UserData->password())), array('id_user' => $this->getWayPattern(0)));

                if($result > 0)
                {
                    $this->Data->add_notification('password_changed', 'admin');
                }
                else
                {
                    $this->Data->add_notification('not_changed', 'admin');
                }

                if($UserData->notifyUser())
                {
                    $email = $info['email'];

                    if($email != '')
                    {
                        $mail = 'На сайте Центра Микрофинансирования изменен ваш пароль для входа в личный кабинет https://ukcm.ru/cabinet/'.PHP_EOL.PHP_EOL;

                        $mail .= 'Логин: '.$info['username'].PHP_EOL.'Пароль: ';
                        SendMail::send(
                            $email,
                            'Изменение данных для доступа в кабинет на сайте Центра Микрофинансирования',
                            $mail.$UserData->password()
                        );
                        Mail::log($info['city_id'], $mail.'***');
                    }
                    else
                    {
                        $this->Data->add_error('mail_not_exists', 'admin');
                    }
                }
            }
            else
            {
                $this->Data->add_error('re_password', 'admin');
            }

        }
    }
}

class User extends ActiveRecord
{
    function __construct($userId)
    {
        $this->sql("SELECT u.id_user, u.username, u.email AS user_email, u.city_user, u.city_id, u.active, u.created FROM `users` AS u");

        $this->where('id_user', $userId);
    }

    public function iterator()
    {
        return $this->asArray();
    }
}

class UserData extends Mod
{
    /**
     * @Post()
     * @Validate(type="password")
     */
    private $password;

    /**
     * @Post()
     * @Validate(type="equals", between="{{password}}")
     */
    private $repassword;
    /**
     * @Post(is_checkbox="true")
     */
    private $send_password = false;

    public function __construct()
    {
        parent::__construct();

        $this->initModifiers();
    }

    public function password()
    {
        return $this->password;
    }

    public function notifyUser()
    {
        return $this->send_password;

    }

    public function passwordChange()
    {
        return $this->noErrorsOn('Post');
    }

    public function canChangePassword()
    {
        return $this->noError();
    }
}