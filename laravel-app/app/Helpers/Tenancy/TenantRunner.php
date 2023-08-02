<?php

declare(strict_types=1);

namespace App\Helpers\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TenantRunner
{
    protected array $data = [];
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function make(array $data = []): static
    {
        return new static($data);
    }

    /**
     * Run a callback in this tenant's context.
     * Atomic, safely reverts to previous context.
     *
     * @param callable $callback
     * @return mixed
     */
    public function run(Tenant $tenant, callable $callback)
    {
        $defaultConnection = config('database.default');

        if (!$tenant || !$tenant?->id) {
            return null;
        }

        $databaseSchema = ($tenant?->tenancy_db_name ?? null) ?: null;

        $databaseSchema ??= $tenant?->id ? config('tenant-sets.default_db_prefix') . $tenant?->id : null;

        if (!$databaseSchema) {
            Config::set('database.default', $defaultConnection);
            return null;
        }

        $tenantConnectionBase = config('tenant-sets.tenant_connection_base') ?: config('database.default');

        // Define a nova conexão
        DB::purge($tenantConnectionBase);
        DB::purge(config('database.default'));

        Config::set('database.default', $tenantConnectionBase);

        Config::set('database.connections.' . $tenantConnectionBase . '.search_path', $databaseSchema);

        // Retorna a conexão atualizada
        \DB::reconnect($tenantConnectionBase);

        $result = $callback($this, $tenant);

        Config::set('database.default', $defaultConnection);

        return $result;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
