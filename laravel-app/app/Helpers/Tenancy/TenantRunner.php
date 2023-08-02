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
     * @param Tenant $tenant
     * @param callable $callback
     *
     * @return mixed
     */
    public function run(Tenant $tenant, callable $callback)
    {
        $defaultConnection = config('database.default_bkp') ?: config('database.default');

        if (!$tenant || !$tenant?->id) {
            return null;
        }

        $databaseSchema = ($tenant?->tenancy_db_name ?? null) ?: null;

        $databaseSchema ??= $tenant?->id ? config('tenant-sets.default_db_prefix') . $tenant?->id : null;

        if (!$databaseSchema) {
            Config::set('database.default', $defaultConnection);
            Config::offsetUnset('database.default_bkp');

            return null;
        }

        if (!config('database.default_bkp')) {
            Config::set('database.default_bkp', $defaultConnection);
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
        Config::offsetUnset('database.default_bkp');

        return $result;
    }

    /**
     * Initialize a tenant's context for all.
     *
     * @param Tenant $tenant
     *
     * @return mixed
     */
    public function initialize(Tenant $tenant)
    {
        $defaultConnection = config('database.default_bkp') ?: config('database.default');

        if (!$tenant || !$tenant?->id) {
            return false;
        }

        $databaseSchema = ($tenant?->tenancy_db_name ?? null) ?: null;

        $databaseSchema ??= $tenant?->id ? config('tenant-sets.default_db_prefix') . $tenant?->id : null;

        if (!$databaseSchema) {
            Config::offsetUnset('database.default_bkp');

            return false;
        }

        if (!config('database.default_bkp')) {
            Config::set('database.default_bkp', $defaultConnection);
        }

        $tenantConnectionBase = config('tenant-sets.tenant_connection_base') ?: config('database.default');

        $tenantConnectionBaseConfig = config('database.connections.' . $tenantConnectionBase);

        $tempConfigName = 'tenant_temp_connection';

        Config::set('database.connections.' . $tempConfigName, $tenantConnectionBaseConfig);
        Config::set('database.connections.' . $tempConfigName . '.search_path', $databaseSchema);

        // Define a nova conexão
        DB::setDefaultConnection($tempConfigName);
        DB::purge($tempConfigName);

        \DB::reconnect($tempConfigName);

        return true;
    }

    /**
     * Ends tenant's context for all.
     *
     * @return mixed
     */
    public function end()
    {
        $defaultConnection = config('database.default_bkp') ?: config('database.default');

        if (!$defaultConnection) {
            return false;
        }

        Config::offsetUnset('database.default_bkp');

        $tempConfigName = 'tenant_temp_connection';

        // Define a nova conexão
        DB::purge($tempConfigName);
        DB::purge($defaultConnection);
        DB::setDefaultConnection($defaultConnection);
        DB::purge(config('database.default'));

        Config::offsetUnset('database.connections.' . $tempConfigName);

        \DB::reconnect(config('database.default'));

        return true;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
