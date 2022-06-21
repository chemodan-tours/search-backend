<?php

namespace App\Search\Alean;

enum AleanResponseEnum: string
{
    case SUCCESS = 'lrSuccess';
    case ACCESS_DENIED = 'lrAccessDenied';
}
