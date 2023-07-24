<?php

namespace App\IOData\InputHandlers\LambdaRequest;

use Illuminate\Support\Collection;
use App\Helpers\Array\ArrayHelpers;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\PendingRequest;
use App\IOData\DataMutators\Resources\ResourceManager;

class EventHandler
{
    public function __construct(
        protected array $event
    ) {
        //
    }

    public function getRecords(): Collection
    {
        return collect($this->event['Records'] ?? []);
    }

    public function getFirstRecord(): Collection
    {
        return collect($this->getRecords()->first());
    }

    /**
     * getMessageAttribute function
     *
     * @param array $messageAttributes
     * @param string $key
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public static function getMessageAttribute(
        array $messageAttributes,
        string $key,
        ?string $alterKey = null,
        mixed $defaultValue = null,
        bool $stringToArray = false,
    ): mixed {
        $alterKey ??= $key;

        $value = $messageAttributes[$key]['StringValue'] ?? $messageAttributes[$alterKey]['StringValue'] ?? $defaultValue ?? null;

        if (!$stringToArray) {
            return $value;
        }

        return ArrayHelpers::stringToArray((string) $value);
    }

    public function processRecord(Collection $recordData): Collection
    {
        $processRecordReturnData = [
            'fail' => false,
            'success' => false,
        ];

        $messageAttributes = $recordData->get('MessageAttributes') ?? $recordData->get('messageAttributes') ?? [];
        $requestType = EventHandler::getMessageAttribute($messageAttributes, 'requestType');
        $user_id_type = EventHandler::getMessageAttribute($messageAttributes, 'user_id_type');
        $user_id = EventHandler::getMessageAttribute($messageAttributes, 'user_id');
        $tenant_id = EventHandler::getMessageAttribute($messageAttributes, 'tenant_id');
        $resource = EventHandler::getMessageAttribute($messageAttributes, 'resource');
        $mappedColumns = EventHandler::getMessageAttribute($messageAttributes, 'mappedColumns', stringToArray: true);
        $modifiers = EventHandler::getMessageAttribute($messageAttributes, 'modifiers', stringToArray: true);
        $callbackUrl = EventHandler::getMessageAttribute($messageAttributes, 'callbackUrl');

        $validatedData = static::validateRecord([
            'requestType' => $requestType,
            'user_id_type' => $user_id_type,
            'user_id' => $user_id,
            'tenant_id' => $tenant_id,
            'resource' => $resource,
            'mappedColumns' => $mappedColumns,
            'modifiers' => $modifiers,
            'callbackUrl' => $callbackUrl,
        ]);

        if ($validatedData['error'] ?? null) {
            return collect($validatedData);
        }

        $success = true; // TODO

        $clientResponse = $this->callbackToRequester(collect($validatedData['validated'] ?? null));

        if ($clientResponse) {
            $processRecordReturnData['clientResponse']['ok'] = $clientResponse->ok();
            $processRecordReturnData['clientResponse']['body'] = $clientResponse->json();
        }

        return collect($processRecordReturnData ?? []);
    }

    public static function validateRecord(array $data = [], bool $throw = false): array
    {
        try {
            /**
             * @var \Illuminate\Validation\Validator $Validator
             */
            $validator = \Validator::make($data, [
                'requestType' => 'required|string|in:import,export',
                'user_id_type' => 'required|string|in:id,email',
                'user_id' => 'required|string',
                'tenant_id' => 'string',
                'resource' => 'required|string|in:' . implode(',', array_keys(ResourceManager::resourceList())),
                'mappedColumns' => 'required|array',
                'modifiers' => 'required|array',
                'callbackUrl' => 'required|url',
            ]);

            $validator->validate();

            return [
                'validated' => $validator?->validated() ?? [],
                'error' => null,
            ];
        } catch (\Throwable $th) {
            \Log::error([$th->getMessage(), currentFileAndLine(true)]);

            if ($throw) {
                throw $th;
            }

            return [
                'validated' => null,
                'error' => $th->getMessage(),
            ];
        }
    }

    protected function callbackToRequester(Collection $data, bool $throw = false): null|PendingRequest|Response
    {
        try {
            $success = $data->get('success', null);
            $requestType = $data->get('requestType');
            $callbackUrl = $data->get('callbackUrl');

            if (
                !is_bool($success)
                || !in_array($requestType, ['import', 'export'])
                || !filter_var($callbackUrl, FILTER_VALIDATE_URL)
            ) {
                if ($throw) {
                    throw new \Exception(
                        spf('Fail [%s]', currentFileAndLine(true)),
                        1
                    );
                }

                return null;
            }

            $types = [
                'export' => __('import-export.export', [], 'pt_BR'),
                'import' => __('import-export.export', [], 'pt_BR'),
            ];

            $translatedType = $types[$requestType] ?? 'importação/exportação';

            $messageTitle = $success
                ? __('import-export.success_title', ['type' => $translatedType], 'pt_BR')
                : __('import-export.fail_title', ['type' => $translatedType], 'pt_BR');

            $messageBody = $success
                ? __('import-export.success_body', ['type' => $translatedType], 'pt_BR')
                : __('import-export.fail_body', ['type' => $translatedType], 'pt_BR');
            return Http::withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
            ])->asJson()->post($callbackUrl, [
                'MessageAttributes' => [
                    'status' => [
                        'DataType' => 'String',
                        'StringValue' => var_export($success, true)
                    ],
                    'user_id_type' => [
                        'DataType' => 'String',
                        'StringValue' => 'email'
                    ],
                    'user_id' => [
                        'DataType' => 'String',
                        'StringValue' => 'ti@compart.com.br'
                    ],
                    'tenant_id' => [
                        'DataType' => 'String',
                        'StringValue' => 'catupiry'
                    ],
                    'messageBody' => [
                        'DataType' => 'String',
                        'StringValue' => $messageBody,
                    ],
                    'messageTitle' => [
                        'DataType' => 'String',
                        'StringValue' => $messageTitle,
                    ],
                    'requestType' => [
                        'DataType' => 'String',
                        'StringValue' => $requestType,
                    ],
                    'reportFileUrl' => [
                        'DataType' => 'String',
                        'StringValue' => 'http://google.com#reportFileUrl'
                    ],
                    'exportFileUrl' => [
                        'DataType' => 'String',
                        'StringValue' => 'http://google.com#exportFileUrl'
                    ]
                ]
            ]);
        } catch (\Throwable $th) {
            \Log::error([$th->getMessage(), currentFileAndLine(true)]);

            if ($throw) {
                throw $th;
            }

            return null;
        }
    }
}
