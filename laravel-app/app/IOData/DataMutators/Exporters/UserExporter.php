<?php

namespace App\IOData\DataMutators\Exporters;

use Closure;
use App\RequestInfo\User;
use Illuminate\Contracts\Support\Arrayable;
use App\IOData\DataMutators\RequestInfo\RequestInfo;

class UserExporter extends Exporter
{
    public function __construct(
        protected RequestInfo $requestInfo,
        protected mixed $options = null
    ) {
        # Demo
        // parent::loadInit();
    }

    protected function before()
    {
        # Demo
        parent::before();
    }

    protected function handle()
    {
        # Demo
        $this->process();
    }

    protected function process()
    {
        # Demo
        parent::process();
    }

    protected function after()
    {
        # Demo
        parent::after();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
    {
        # Demo
        return User::query();
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
            'name' => [
                'table_column' => 'name',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'email' => [
                'table_column' => 'email',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'email_verified_at' => [
                'table_column' => 'email_verified_at',
                'label' => null,
                'relationships' => null,
                'format' => null,
            ],
            'creator' => [
                'table_column' => 'created_by',
                'label' => 'Criado por',
                'relationships' => [
                    'creator',
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
            'creator' => fn ($query) => $query->select('id', 'name'),
        ];

        return $relationships[$relationship] ?? null;
    }
}
