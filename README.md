# GCP Request Logger for Laravel

The GCP Request Logger package is designed to store logs of every App Engine request within BigQuery. This includes detailed information such as peak memory and SQL queries. The primary purpose of this tool is to optimize memory usage within the App Engine.

## Installation

Follow the steps below to successfully install the package:

1. Install the package via composer:

``` bash
composer require firevel/request-logger
```

2. Propagate the `request-logger.php` configuration file by executing the following command:
``` bash
php artisan vendor:publish --provider="Firevel\RequestLogger\Providers\RequestLoggerServiceProvider"
```

3. Establish a BigQuery dataset named `requests` and create a table `api`. Ensure to set up the table schema as in [schema.json file](https://github.com/firevel/request-logger/blob/main/src/schema.json).

4. Assign the `BigQuery Data Editor` role to your App Engine service account ({project}@appspot.gserviceaccount.com) for appropriate access.

5. Add `\Firevel\RequestLogger\Middleware\LogRequest::class` middleware into your `App\Http\Kernel.php` file.

### Configuration
Additional configurations can be set up in the `config/request-logger.php` file.

### How it works.

Post installation and middleware inclusion, every request will be logged and stored in the api table within your requests dataset in the BigQuery. This logging will help you gain insights and further enhance the performance of your App Engine.
