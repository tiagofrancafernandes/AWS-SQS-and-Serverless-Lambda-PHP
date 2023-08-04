<?php

namespace App\Models;

use App\Models\TenantDomain;
use App\Models\Traits\PreventUpdate;
use App\Helpers\Tenancy\TenantRunner;
use App\Models\Traits\StringPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class Tenant extends Model
{
    use StringPrimaryKey;
    use PreventUpdate;

    protected $connection = 'comptrade_central';
    protected $table = 'tenants';

    protected $casts = [
        'data' => AsCollection::class,
    ];

    /**
     * Get all of the domains for the Tenant
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class, 'tenant_id', 'id');
    }

    public function __get($key)
    {
        if (!$key) {
            return null;
        }

        return $this->getAttribute($key) ?? $this->data?->get($key) ?? null;
    }

    public function initialize()
    {
        return TenantRunner::make()->initialize($this);
    }

    public static function initializeById(string $id)
    {
        return TenantRunner::make()->initialize(static::find($id));
    }

    public static function end()
    {
        return TenantRunner::make()->end();
    }
}
