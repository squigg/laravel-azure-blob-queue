Windows Azure Storage Queue driver for Laravel
==============================================

#### Installation

Require this package in your `composer.json`:

### Laravel 4.x
	"stayallive/laravel-azure-blob-queue": "1.*"
### Laravel 5.x
    "stayallive/laravel-azure-blob-queue": "2.*"

Add the following pear repository in your `composer.json` required by the Azure SDK:

    "repositories": [
        {
            "type": "pear",
            "url": "http://pear.php.net"
        }
    ],

Run composer update!

After composer update is finished you need to add ServiceProvider to your `providers` array in `app/config/app.php`:

	'Stayallive\LaravelAzureBlobQueue\Support\Serviceprovider',

add the following to the `connection` array in `app/config/queue.php`, set your `default` connection to `azure` and fill out your own connection data from the Azure Management portal:

	'azure' => array(
        'driver'        => 'azure.blob',    
        'protocol'      => 'https'          // https or http
        'accountname'   => '',              // Your storage account name
        'key'           => '',              // Access key
        'queue'         => '',              // Queue container name
        'timeout'       => 60               // Timeout (seconds) before a job is released back to the queue
    )

#### Usage
Once you completed the configuration you can use Laravel Queue API. If you used other queue drivers you do not need to change anything else. If you do not know how to use Queue API, please refer to the official Laravel [documentation](http://laravel.com/docs/queues).

This queue driver will not accept a closure, you should use classes to handle the queue messages!

#### Contribution
You can contribute to this package by opening issues/pr's. Enjoy!
