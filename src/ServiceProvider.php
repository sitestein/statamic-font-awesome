<?php

namespace Sitestein\FontAwesome;

use Sitestein\FontAwesome\FieldTypes\FontAwesome as FontAwesomeFieldType;
use Statamic\Providers\AddonServiceProvider;
use Sitestein\FontAwesome\Tags\FontAwesomeTags;

class ServiceProvider extends AddonServiceProvider
{
    protected $scripts = [
        __DIR__.'/../public/js/font-awesome_cp.js'
    ];

    protected $fieldtypes = [
        FontAwesomeFieldType::class
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $tags = [
        FontAwesomeTags::class
    ];

    public function bootAddon(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $this->registerExternalScript(FontAwesome::kit()->get('url'));
    }
}
