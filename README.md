PgEnergy
============

ZF2 Energy Module

Installation
------------

### Main Setup

#### By cloning project

1. Install the [PgEnergy](https://github.com/earlhickey/PgEnergy) ZF2 module
   by cloning it into `./vendor/`
2. Clone this project into your `./vendor/` directory

#### With composer

1. Add this project in your composer.json:

    ```json
    "require": {
        "earlhickey/zf2-energy": "~1"
    }
    ```

2. Now tell composer to download PgEnergy by running the command:

    ```bash
    $ php composer.phar update
    ```

#### Post installation

1. Enabling it in your `application.config.php` file

    ```php
    <?php
    return array(
        'modules' => array(
            // ...
            'Energy',
        ),
        // ...
    );
    ```

2. Add your database to global.php

    ```php
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => function ($sm) {
                return new Zend\Db\Adapter\Adapter(array(
                    'driver' => 'Pdo_Sqlite',
                    'database' => '/path/to/db'
                ));
            },
        ),
    ),
    ```

3. Install cronjobs for storing energy usage

    ```bash
    $ crontab -e
    # Save current energie usage every 3 minutes
    1-59/3 * * * * /usr/bin/python /path/to/zf-energy/docs/read-p1.py >> /dev/null 2>&1
    # Save daily usage at 1:55
    55 1 * * * /usr/bin/python /path/to/zf-energy/docs/day.py >> /dev/null 2>&1
    ```
