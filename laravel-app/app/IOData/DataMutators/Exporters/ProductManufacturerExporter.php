<?php

namespace App\IOData\DataMutators\Exporters;

use Closure;
use App\Models\Tenant;
use App\Helpers\Tenancy\TenantRunner;
use Illuminate\Contracts\Support\Arrayable;
use App\IOData\DataMutators\RequestInfo\RequestInfo;

class ProductManufacturerExporter extends Exporter
{
    protected ?Tenant $tenant = null;

    public function __construct(
        protected RequestInfo $requestInfo,
        protected mixed $options = null
    ) {
        $this->tenant = Tenant::find($requestInfo->getTenantId());
    }

    protected function handle()
    {
        $this->process();
    }

    protected function process()
    {
        if (!$this->tenant) {
            parent::process();
            return;
        }

        $output = TenantRunner::make([
            'requestInfo' => $this->requestInfo,
            'tenant' => $this->tenant,
            'exporter' => $this,
        ])
            ->run(
                $this->tenant,
                function (TenantRunner $runner) {
                    $params = $runner->getData(); // Exemplo de recuperação dos parâmetros

                    return parent::process();
                }
            );

        dump($output);
    }

    protected function after()
    {
        # Demo
        parent::after();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
    {
        return \App\IOData\DataMutators\Models\Comptrade\ProductManufacturer::query();
    }

    public static function getAllowedColumns(): array
    {
        # Demo
        return [
            'id' => [
                'table_column' => 'id',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'uuid' => [
                'table_column' => 'uuid',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'name' => [
                'table_column' => 'name',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'is_active' => [
                'table_column' => 'is_active',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'created_at' => [
                'table_column' => 'created_at',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'updated_at' => [
                'table_column' => 'updated_at',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'deleted_at' => [
                'table_column' => 'deleted_at',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
        ];
    }

    public static function getRelationshipData(?string $relationship = null): null|string|Closure
    {
        if (!$relationship) {
            return null;
        }

        $relationships = [
            //
        ];

        return $relationships[$relationship] ?? null;
    }
}
