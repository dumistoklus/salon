<?php
namespace Orgup\Common\Modifiers;

use Orgup\Common\Modifier;
use \Orgup\Common\Modifiers\Validator\Email;
use \Orgup\Common\Modifiers\Validator\Login;
use \Orgup\Common\Modifiers\Validator\Password;
use \Orgup\Common\Modifiers\Validator\Numeric;
use \Orgup\Common\Modifiers\Validator\Equals;
use \Orgup\Common\Modifiers\Validator\Length;
use \Orgup\Common\Modifiers\Validator\Match;
use \Orgup\Common\Modifiers\Validator\Word;
use \Orgup\Common\Modifiers\Validator\Phone;
use Orgup\Common\Modifiers\Validator\NumericOrVoid;

class Validate extends Modifier
{
    public $type;
    public $between;

    protected function createResult()
    {
        switch($this->type)
        {
            case 'email':
                    Email::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            case 'login':
                    Login::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            case 'password':
                    Password::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            case 'numeric':
                    Numeric::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            case 'equals':
                    Equals::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            case 'length':
                    Length::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            case 'match':
                    Match::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            case 'word':
                    Word::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            case 'phone':
                    Phone::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            case 'numericOrVoid':
                    NumericOrVoid::validate($this->value, $this->between, $this->property_name, $this->object);
                break;
            default:
                $this->object->set_error('Validate', 'unsupported '.$this->type);
        }

        return $this->value;
    }
}