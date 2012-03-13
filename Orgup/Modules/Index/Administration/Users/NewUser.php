<?php
namespace Orgup\Modules\Index\Administration\Users;
use \Orgup\Modules\Index\Administration\AdministrationModuleBuilder;
use \Orgup\Common\Hash;
use \Orgup\Common\AddUser;
use \Orgup\Application\Redirect;
use Orgup\Plugins\SendMail;
use Orgup\Plugins\RequestManager\Mail;

class NewUser extends AdministrationModuleBuilder {
    /**
    *@var \Orgup\DataModels\Index\Administration\Users\NewUser
    */
    protected $Data;

    private $userId;

    public function run() {

        if($this->Data->isEditing() && $this->Data->noErrors())
        {
            try {
                $this->createUser();

                if($this->Data->getSendEmail())
                {
                    $email = $this->Data->getEmail();

                    if(filter_var($email, FILTER_VALIDATE_EMAIL))
                    {
                        $mail = 'На сайте Центра Микрофинансирования был зарегистрирован пользователь на ваш адрес почты.'.PHP_EOL.PHP_EOL;

                        $mail .= 'Логин: '.$this->Data->getName().PHP_EOL.'Пароль: ';
                        SendMail::send(
                            $email,
                            'Регистрация на сайте Центра Микрофинансирования',
                            $mail.$this->Data->getPassword()
                        );
                        Mail::log($this->Data->getCityId(), $mail.'***');
                    }
                }

                throw new Redirect($this->Ways->admin_user_rules($this->userId));
            }
            catch(\PDOException $e)
            {
                $this->Data->add_error('user_exists', 'admin');
            }
        }
    }

    private function createUser()
    {
        $User = AddUser::add(array(
                                    'username' => $this->Data->getName(),
                                    'password' => Hash::password($this->Data->getPassword()),
                                    'email' => $this->Data->getEmail(),
                                    'created' => time(),
                                    'city_user' => "0"
                               ));

            $this->userId = $User->getId();
            return true;

    }
}
