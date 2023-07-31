<?php

namespace App\IOData\DataMutators\Models\Comptrade;

use App\Models\User;

class ComptradeUser extends User
{
    protected $connection = 'comptrade_central';
    protected $table = 'public.users';
}
