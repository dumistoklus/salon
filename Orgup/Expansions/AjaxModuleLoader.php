<?php
namespace Orgup\Expansions;
use Orgup\Application\ModuleLoader;

class AjaxModuleLoader extends ModuleLoader {

    protected $defaultDataClass = 'Orgup\DataModels\AjaxData';

    protected function ifUserNotMemberAndNeedRights() {
        //todo: здесь можно реализовать поведение для незарег. пользователя, который забрел в аякс модуль, требующий права
    }
}