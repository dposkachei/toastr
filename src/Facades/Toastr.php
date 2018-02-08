<?php

namespace Bulk\Toastr\Facades;

use Illuminate\Support\Facades\Facade;
use Bulk\Toastr\Toastr as ToastrService;

class Toastr extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ToastrService::class;
    }

}
