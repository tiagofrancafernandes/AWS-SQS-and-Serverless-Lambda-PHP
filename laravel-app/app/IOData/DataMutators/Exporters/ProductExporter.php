<?php

namespace App\IOData\DataMutators\Exporters;

use Closure;
use App\Models\Tenant;
use App\Helpers\Tenancy\TenantRunner;
use Illuminate\Contracts\Support\Arrayable;
use App\IOData\DataMutators\RequestInfo\RequestInfo;

class ProductExporter extends Exporter
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
        return \App\IOData\DataMutators\Models\Comptrade\Product::query();
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
            'barcode' => [
                'table_column' => 'barcode',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'sku' => [
                'table_column' => 'sku',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'customer_sku' => [
                'table_column' => 'customer_sku',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'is_competitor' => [
                'table_column' => 'is_competitor',
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
            'product_category_id' => [
                'table_column' => 'product_category_id',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'product_brand_id' => [
                'table_column' => 'product_brand_id',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'product_type_id' => [
                'table_column' => 'product_type_id',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'product_manufacturer_id' => [
                'table_column' => 'product_manufacturer_id',
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
            'brand' => [
                'table_column' => 'product_brand_id',
                'label' => 'brand',
                'relationships' => [
                    'brand',
                ],
                'format' => function (Arrayable $data = null): string {
                    $data = static::fluent($data?->toArray());

                    return $data?->get('name') ?: '';
                },
            ],
            'category' => [
                'table_column' => 'product_category_id',
                'label' => 'category',
                'relationships' => [
                    'category',
                ],
                'format' => function (Arrayable $data = null): string {
                    $data = static::fluent($data?->toArray());

                    return $data?->get('name') ?: '';
                },
            ],
            'type' => [
                'table_column' => 'product_type_id',
                'label' => 'type',
                'relationships' => [
                    'type',
                ],
                'format' => function (Arrayable $data = null): string {
                    $data = static::fluent($data?->toArray());

                    return $data?->get('name') ?: '';
                },
            ],
            'manufacturer' => [
                'table_column' => 'product_manufacturer_id',
                'label' => 'manufacturer',
                'relationships' => [
                    'manufacturer',
                ],
                'format' => function (Arrayable $data = null): string {
                    $data = static::fluent($data?->toArray());

                    return $data?->get('name') ?: '';
                },
            ],
        ];
    }

    public static function getRelationshipData(?string $relationship = null): null|string|Closure
    {
        # Demo
        if (!$relationship) {
            return null;
        }

        $relationships = [
            'brand' => fn ($query) => $query->select('id', 'name'),
            'category' => fn ($query) => $query->select('id', 'name'),
            'type' => fn ($query) => $query->select('id', 'name'),
            'manufacturer' => fn ($query) => $query->select('id', 'name'),
        ];

        return $relationships[$relationship] ?? null;
    }
}
