<?php

namespace Firesphere\SearchBackend\Admins;

use Firesphere\SearchBackend\Models\DirtyClass;
use Firesphere\SearchBackend\Models\SearchLog;
use Firesphere\SearchBackend\Models\SearchSynonym;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\View\Requirements;

/**
 * Class \Firesphere\SearchBackend\Admins\SearchAdmin
 *
 */
class SearchAdmin extends ModelAdmin
{
    /**
     * @var string Add a pretty magnifying glass to the sidebar menu
     */
    private static $menu_icon_class = 'font-icon-search';

    /**
     * @var string Where to find me
     */
    private static $url_segment = 'searchadmin';

    /**
     * @var string My name
     */
    private static $menu_title = 'Search';

    /**
     * List of all managed {@link DataObject}s in this interface. {@link ModelAdmin::$managed_models}
     *
     * @config
     * @var array|string
     */
    private static $managed_models = [
        SearchSynonym::class,
        SearchLog::class,
        DirtyClass::class
    ];


    /**
     * Make sure the custom CSS for highlighting in the GridField is loaded
     */
    public function init()
    {
        parent::init();

        Requirements::css('firesphere/searchbackend:client/dist/main.css');
    }
}
