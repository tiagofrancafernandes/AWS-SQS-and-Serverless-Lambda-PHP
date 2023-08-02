<?php

namespace App\IOData\DataMutators\Models\Comptrade;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;

    protected $connection = 'comptrade_tenant_base';
    protected $table = 'products';

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }
    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id', 'id');
    }
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(ProductManufacturer::class, 'product_manufacturer_id', 'id');
    }
}
