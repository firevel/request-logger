<?php

namespace Firevel\RequestLogger\Jobs;

use Firevel\RequestLogger\Services\QueryLogger;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Request
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $payload =  $this->requestToPayload($this->getRequest());

        $this->insertRow($payload);
    }

    /**
     * Transform Request object to array with log payload.
     *
     * @param  Request $request
     * @return array
     */
    public function requestToPayload(Request $request): array
    {
        // Use request log id or random integer for id.
        $id = ! empty($headers['x-appengine-request-log-id']) ? $headers['x-appengine-request-log-id'][0] : $this->randomId();

        $payload = [
            'id' => $id,
            'platform' => 'gae',
            'runtime' => env('GAE_RUNTIME'),
            'service' => env('GAE_SERVICE'),
            'instance_id' => env('GAE_INSTANCE'),
            'version' => env('GAE_VERSION'),
            'method' => $request->method(),
            'host' => $request->getHost(),
            'path' => $request->path(),
            'execution_time' => round(microtime(true) - LARAVEL_START, 4),
            'executed_at' => now(),
            'tmp_size' => $this->getTmpSize(),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_available' => env('GAE_MEMORY_MB') * 1000000, // use bytes
        ];

        if (config('request-logger.log.ip')) {
            $payload['ip'] = $request->ip();
        }

        if (config('request-logger.log.user') && $user = $request->user()) {
            $payload['user'] = $user->toJson();
        }

        if (config('request-logger.log.parameters')) {
            $parameters = $this->filter(
                $request->all(),
                config('request-logger.filtered.parameters', [])
            );
            $payload['parameters'] = json_encode($parameters);
        }

        if (config('request-logger.log.headers')) {
            $headers = $this->filter(
                $request->header(),
                config('request-logger.filtered.headers', [])
            );
            $payload['headers'] = json_encode($headers);
        }

        if (config('request-logger.log.queries')) {
            $queries = app(QueryLogger::class)->getQueries();

            $payload['queries'] = json_encode($queries);
            $payload['queries_count'] = count($queries);
        }

        return $payload;
    }

    /**
     * Get tmp directory size in bytes.
     *
     * @return integer
     */
    public function getTmpSize() {
        $size = 0;

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(sys_get_temp_dir()));

        foreach ($iterator as $file) {
            $size += $file->getSize();
        }

        return $size; // return in bytes
    }

    /**
     * Filter out certain array elements.
     *
     * @param  array $array
     * @param  array $keys
     * @return array
     */
    public function filter($array, $keys)
    {
        foreach ($keys as $key) {
            if (! empty($array[$key])) {
                $array[$key] = '[FILTERED]';
            }
        }

        return $array;
    }

    /**
     * Generate random id.
     *
     * @return integer
     */
    public function randomId()
    {
        return random_int(3656158440062976, 9007199254740991);
    }

    /**
     * Get request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Insert row to Big Query.
     *
     * @param  array $payload
     * @return void
     */
    public function insertRow($payload)
    {
        $bigQuery = new BigQueryClient([
          'projectId' => env('GOOGLE_CLOUD_PROJECT'),
        ]);
        $dataset = $bigQuery->dataset(config('request-logger.drivers.bigquery.dataset'));
        $table = $dataset->table(config('request-logger.drivers.bigquery.table'));
        $response = $table->insertRows([
            ['data' => $payload]
        ]);

        // You will likely wont see this exception as its handled after response.
        if (! $response->isSuccessful()) {
            throw new \Exception(json_encode($response->failedRows()));
        }
    }
}
