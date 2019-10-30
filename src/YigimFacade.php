<?php

namespace Chameleon\Yigim;

use Illuminate\Support\Facades\Facade;

class YigimFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'yigim';
    }
}
