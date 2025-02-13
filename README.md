# Download Bundle

This bundle allows you to download a database and folders associated with the project from remote host to local machine. 
It is the easiest and easiest way to have the same production data in your development environment.

The bundle works using ssh connections so it is necessary that you have configured to connect through a public key.

**Disclaimer**: This bundle works only for environments with linux.  

## Installation

Download the Bundle.

```bash 
composer require --dev "SheGroup/download-bundle"
```

Enable the Bundle

```php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        // enable it only for dev environment
        if (in_array($this->getEnvironment(), ['dev'], true)) {
            $bundles[] = new SheGroup\DownloadBundle\DownloadBundle();
        }

        // ...
    }

    // ...
}
```

## Configuration

You need put something like this in your config_dev.yml

```yml
download:
    user: 'deploy_user'
    host: 'production_host_or_ip'
    port: 22

    # optional parameter. use it if you want customize max proccess time
    timeout: 300

    database:
        # local directory to save databases
        directory: '%kernel.root_dir%/../var/data/databases'
        
        # optional parameter. use it for tables that you just want to download the structure, not data
        only_structure:
          - 'mail_history'

        remote:
            host: 'production_database_host'
            name: 'production_database_name'
            user: 'production_database_user'
            password: 'production_database_password'           

        local:
            host: '%database_host%'
            name: '%database_name%'
            user: '%database_user%'
            password: '%database_password%'

        # optional parameter. define max number of database files to keep on local directory
        max_local_db: 0

    # some directories that you want download.
    directories:
        web_uploads:
            remote: '/path/to/project/web/uploads'
            local: '%kernel.root_dir%/../web'
            # you can exclude some directories from there
            exclude:
                - 'cache'

        var_data:
            remote: '/path/to/project/var/data'
            local: '%kernel.root_dir%/../var'
            exclude:
                - 'spool'            
            
```

## Usage

### Download

When you execute this command, both the database and the directories are downloaded from the remote environment.

```bash
php bin/console downloader:download
```

This is what you will see in your command line.

![screenshot](https://raw.githubusercontent.com/desarrolla2/download-bundle/master/doc/img/screenshot_1.png)

### Load

Maybe you want to put your local database in a previous state. This bundle keeps a copy of every download you have made, 
so going back to one of these states is very easy.

```bash
php bin/console downloader:load
```

Select from available dates.

![screenshot](https://raw.githubusercontent.com/desarrolla2/download-bundle/master/doc/img/screenshot_2.png)

This is what you will see in your command line.

![screenshot](https://raw.githubusercontent.com/desarrolla2/download-bundle/master/doc/img/screenshot_3.png)

### Delete old databases

Delete old databases from the local directory. The max number of files you can keep is defined by max_local_db parameter

```bash
php bin/console downloader:delete:old
```

## Contact

You can contact with me on [@desarrolla2](https://twitter.com/desarrolla2).
