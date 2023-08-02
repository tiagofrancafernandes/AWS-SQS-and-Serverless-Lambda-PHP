<?php

namespace App\IOData\DataMutators\Models\Comptrade;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBrand extends Model
{
    // use SoftDeletes;

    protected $connection = 'comptrade_tenant_base';
    protected $table = 'product_brands';
}
