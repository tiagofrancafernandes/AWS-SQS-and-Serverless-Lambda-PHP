<?php

namespace App\IOData\InputHandlers\LambdaRequest;

use App\Enums\IORequestStatusEnum;
use App\Models\ExportRequest;
use App\Models\ImportRequest;
use Illuminate\Support\Fluent;
use Illuminate\Support\Collection;
use App\Helpers\Array\ArrayHelpers;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\PendingRequest;
use App\IOData\DataMutators\RequestInfo\RequestInfo;
use App\IOData\DataMutators\Resources\ResourceManager;

class EventHandler
{
    protected Fluent $event;

    public function __construct(array $event)
    {
        $this->event = new Fluent($event ?? []);
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
        $alterKey = $alterKey ?: $key;

        if (!$messageAttributes || (!$key && !$alterKey)) {
            return $defaultValue;
        }

        $data = $messageAttributes[$key] ?? $messageAttributes[$alterKey] ?? null;

        if (!$data) {
            return $defaultValue;
        }

        $binaryListValues = ($data['binaryListValues'] ?? $data['BinaryListValues'] ?? null) ?: null;
        $stringListValues = ($data['stringListValues'] ?? $data['StringListValues'] ?? null) ?: null;
        $stringValue = ($data['stringValue'] ?? $data['StringValue'] ?? null) ?: null;

        if ($binaryListValues) {
            return $binaryListValues;
        }

        if ($stringListValues) {
            return $stringListValues;
        }

        if ($stringValue) {
            if (!$stringToArray) {
                return $stringValue;
            }

            return ArrayHelpers::stringToArray((string) $stringValue);
        }

        return $defaultValue;
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

        $processResult = $this->startProcess(
            $validatedData['validated'] ?? [],
            $recordData,
            config('import-export.lambda_process.throw_on_lambda_process'),
        );

        print_r(['processResult' => $processResult]);

        $processRecordReturnData['processResult'] = $processResult;
        $wasFinishedSuccessfully = $processResult['was_finished_successfully'] ?? null;

        $success = $wasFinishedSuccessfully ?? $processResult['last_run_return']['success'] ?? false;

        $processRecordReturnData['success'] = $success;

        $clientResponse = $this->callbackToRequester(
            collect(array_merge($validatedData['validated'] ?? [], [
                'processResult' => $processResult,
                'success' => $success,
            ]))
        );

        if ($clientResponse) {
            $processRecordReturnData['clientResponse']['ok'] = $clientResponse?->ok() ?? null;
            $processRecordReturnData['clientResponse']['body'] = $clientResponse?->json() ?? null;
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
                'mappedColumns' => 'array',
                'modifiers' => 'array',
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

    protected function callbackToRequester(
        Collection $data,
        bool $throw = false,
    ): null|PendingRequest|Response {
        try {
            $success = $data->get('success', null);
            $requestType = $data->get('requestType');
            $callbackUrl = $data->get('callbackUrl');
            $userIdType = $data?->get('user_id_type');
            $userId = $data?->get('user_id');
            $tenantId = $data?->get('tenant_id');
            $resource = $data?->get('resource');
            $mappedColumns = $data?->get('mappedColumns');
            $modifiers = $data?->get('modifiers');

            $processResult = new Fluent($data->get('processResult', []));

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

            $hasReport = boolval($processResult?->report_url);
            $successBody = $hasReport ? 'import-export.success_body_with_report' : 'import-export.success_body';
            $failBody = $hasReport ? 'import-export.fail_body_with_report' : 'import-export.fail_body';

            $messageTitle = $success
                ? __('import-export.success_title', ['type' => $translatedType], 'pt_BR')
                : __('import-export.fail_title', ['type' => $translatedType], 'pt_BR');

            $messageBody = $success
                ? __($successBody, ['type' => $translatedType], 'pt_BR')
                : __($failBody, ['type' => $translatedType], 'pt_BR');

            fwrite(STDOUT, print_r([
                'callbackUrl' => $callbackUrl,
                'currentFileAndLine' => currentFileAndLine(true),
            ], true) . PHP_EOL);

            return Http::withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
            ])->asJson()->post($callbackUrl, [
                'MessageAttributes' => [
                    'success' => [
                        'DataType' => 'String',
                        'StringValue' => $success
                    ],
                    'finished_successfully' => [
                        'DataType' => 'String',
                        'StringValue' => boolval($processResult?->was_finished_successfully)
                    ],
                    'status' => [
                        'DataType' => 'String',
                        'StringValue' => intval($processResult['last_run_return']['status'] ?? null) ?: null,
                    ],
                    'user_id_type' => [
                        'DataType' => 'String',
                        'StringValue' => $userIdType
                    ],
                    'user_id' => [
                        'DataType' => 'String',
                        'StringValue' => $userId
                    ],
                    'tenant_id' => [
                        'DataType' => 'String',
                        'StringValue' => $tenantId
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
                    'resource' => [
                        'DataType' => 'String',
                        'StringValue' => $resource,
                    ],
                    'reportFileUrl' => [
                        'DataType' => 'String',
                        'StringValue' => $processResult?->report_url,
                    ],
                    'exportFileUrl' => [
                        'DataType' => 'String',
                        'StringValue' => $processResult?->final_file_url
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

    protected function startProcess(
        ?array $data,
        ?Collection $recordData = null,
        bool $throw = false
    ): ?array {
        $toReturn = [
            'errors' => [],
        ];

        try {
            if (!$data) {
                $toReturn['errors'][] = 'Invalid "data"';

                return $toReturn;
            }

            $data = new Fluent($data);

            $invalidItems = array_keys(
                array_filter([
                    'resource' => !$data?->resource,
                    'requestType' => !$data?->requestType,
                    'requestType' => !$data?->requestType,
                    'user_id_type' => !$data?->user_id_type,
                    'user_id' => !$data?->user_id,
                    // 'mappedColumns' => !$data?->mappedColumns,
                    // 'tenant_id' => !$data?->tenant_id,
                    // 'modifiers' => !$data?->modifiers,
                ])
            );

            if ($invalidItems) {
                $toReturn['errors'][] = 'Invalid items: ' . implode(',', $invalidItems);

                return $toReturn;
            }

            $resourceMutator = ResourceManager::getActionProcessor($data?->resource, $data?->requestType);

            if (!$resourceMutator) {
                $toReturn['errors'][] = 'Invalid "resourceMutator"';

                return $toReturn;
            }

            $sqsMessageId = $recordData->get('messageId');
            $sqsMessageBody = $recordData->get('body');
            $sqsRequestInfo = $recordData->get('attributes');
            $messageAttributes = $recordData->get('MessageAttributes') ?? $recordData->get('messageAttributes') ?? null;

            $requestModelData = [
                'resource_name' => $data?->resource,
                'tenant_id' => $data?->tenant_id,
                'mapped_columns' => $data?->mappedColumns,
                'modifiers' => $data?->modifiers,
                'request_date' => now(),
                'status' => IORequestStatusEnum::CREATED,
                'user_id_type' => $data?->user_id_type,
                'user_id' => $data?->user_id,
                'sqs_message_id' => $sqsMessageId,
                'sqs_message_body' => $sqsMessageBody,
                'sqs_request_info' => $sqsRequestInfo,
                'sqs_message_attributes' => $messageAttributes,
            ];

            if ($data?->requestType === 'import') {
                $toReturn['errors'][] = '"import" ainda não está habilitado';

                return $toReturn;
            }

            $requestModel = RequestInfo::createRequestModel(
                $data?->requestType,
                $requestModelData,
            );

            if (!$requestModel) {
                $toReturn['errors'][] = 'Fail to create a "requestModel"';

                return $toReturn;
            }

            $toReturn['request_Model_id'] = $requestModel->{'id'} ?? null;

            $mutatorInstance = new $resourceMutator(new RequestInfo($requestModel));

            if (!$mutatorInstance) {
                $toReturn['errors'][] = 'Fail to get "mutatorInstance"';

                return $toReturn;
            }

            $debugMode = true; // TODO
            $mutatorInstance
                ->debug($debugMode)
                ->runProcess();

            $toReturn['last_run_return'] = $mutatorInstance?->getLastRunReturn();
            $toReturn['was_finished_successfully'] = $requestModel?->{'was_finished_successfully'} ?? null;
            $toReturn['final_file_url'] = $requestModel->getFinalFileUrl();
            $toReturn['report_url'] = $requestModel->getReportUrl();

            return $toReturn ?? [];
        } catch (\Throwable $th) {
            \Log::error($th);

            if ($throw) {
                throw $th;
            }

            $toReturn['errors'][] = $th->getMessage();

            return $toReturn;
        }
    }
}
