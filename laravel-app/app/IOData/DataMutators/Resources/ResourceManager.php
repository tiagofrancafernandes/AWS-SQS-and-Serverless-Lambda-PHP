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
        // TODO
        return [
            'tenant_users' => [
                'export' => \App\IOData\DataMutators\Exporters\UserExporter::class,
                'import' => \App\IOData\DataMutators\Importers\UserImporter::class,
            ],
        ];
    }
}
