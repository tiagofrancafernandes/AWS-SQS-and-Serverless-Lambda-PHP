<?php

namespace App\IOData\DataMutators\Enums;

use App\IOData\DataMutators\Enums\Contracts\CommonEnum;
use App\IOData\DataMutators\Enums\Traits\CommonMethods;

enum RequestTypeEnum: string implements CommonEnum
{
    use CommonMethods;

    case Import = 'import';
    case Export = 'export';
}
