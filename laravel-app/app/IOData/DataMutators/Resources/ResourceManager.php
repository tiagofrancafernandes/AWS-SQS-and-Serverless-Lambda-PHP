<?php

namespace App\IOData\DataMutators\Resources;

abstract class ResourceManager
{
    /**
     * resourceActionList function
     *
     * @param string $resourceName
     *
     * @return array
     */
    public static function resourceActionList(string $resourceName): array
    {
        if (!$resourceName) {
            return [];
        }

        return static::resourceList()[$resourceName] ?? [];
    }

    /**
     * getActionProcessor function
     *
     * @param string $resourceName
     * @param string $actionType
     *
     * @return string|null
     */
    public static function getActionProcessor(
        string $resourceName,
        string $actionType,
    ): ?string {
        if (!$actionType) {
            return null;
        }

        return static::resourceActionList($resourceName)[$actionType] ?? null;
    }

    /**
     * resourceList function
     *
     * @return array
     */
    public static function resourceList(): array
    {
        return [
            'tenant_users' => [
                'export' => \App\IOData\DataMutators\Exporters\UserExporter::class,
                'import' => \App\IOData\DataMutators\Importers\UserImporter::class,
            ],
            'products' => [
                'export' => \App\IOData\DataMutators\Exporters\ProductExporter::class,
                'import' => \App\IOData\DataMutators\Importers\ProductImporter::class,
            ],
            'product_manufacturers' => [
                'export' => \App\IOData\DataMutators\Exporters\ProductManufacturerExporter::class,
                'import' => \App\IOData\DataMutators\Importers\ProductManufacturerImporter::class,
            ],
            // 'query_builder_exporter' => [ // TODO
            //     'export' => \App\IOData\DataMutators\Exporters\QueryBuilderExporter::class,
            //     'import' => \App\IOData\DataMutators\Importers\QueryBuilderExporter::class,
            // ],
            'raw_query_exporter' => [
                'export' => \App\IOData\DataMutators\Exporters\RawQueryExporter::class,
                'import' => \App\IOData\DataMutators\Importers\RawQueryExporter::class,
            ],
        ];
    }
}
