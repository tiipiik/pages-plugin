<?php namespace RainLab\Pages;

use Backend;
use Event;
use System\Classes\PluginBase;
use RainLab\Pages\Classes\Controller;
use RainLab\Pages\Classes\Page as StaticPage;
use RainLab\Pages\Classes\Router;
use Cms\Classes\Theme;

class Plugin extends PluginBase
{

    public function pluginDetails()
    {
        return [
            'name'        => 'Static Pages',
            'description' => 'Pages & menus features.',
            'author'      => 'Alexey Bobkov, Samuel Georges',
            'icon'        => 'icon-files-o'
        ];
    }

    public function registerComponents()
    {
        return [
            '\RainLab\Pages\Components\StaticPage' => 'staticPage',
            '\RainLab\Pages\Components\StaticMenu' => 'staticMenu',
            '\RainLab\Pages\Components\StaticBreadcrumbs' => 'staticBreadcrumbs'
        ];
    }

    public function registerNavigation()
    {
        return [
            'pages' => [
                'label'       => 'rainlab.pages::lang.plugin_name',
                'url'         => Backend::url('rainlab/pages'),
                'icon'        => 'icon-files-o',
                'permissions' => ['rainlab.pages.*'],
                'order'       => 20,

                'sideMenu' => [
                    'pages' => [
                        'label'       => 'rainlab.pages::lang.page.menu_label',
                        'icon'        => 'icon-files-o',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'pages'],
                        'permissions' => ['rainlab.pages.manage_pages'],
                    ],
                    'menus' => [
                        'label'       => 'rainlab.pages::lang.menu.menu_label',
                        'icon'        => 'icon-sitemap',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'menus'],
                        'permissions' => ['rainlab.pages.manage_menus'],
                    ],
                    'textblocks' => [
                        'label'       => 'rainlab.pages::lang.textblock.menu_label',
                        'icon'        => 'icon-file-text-o',
                        'url'         => 'javascript:;',
                        'permissions' => ['rainlab.pages.manage_textblocks'],
                    ]
                ]

            ]
        ];
    }

    public function boot()
    {
        Event::listen('cms.router.beforeRoute', function($url){
            $controller = new Controller();

            return $controller->initCmsPage($url);
        });

        Event::listen('pages.menuitem.listTypes', function() {
            return [
                'static-page'=>'Static page',
                'all-static-pages'=>'All static pages'
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function($type) {
            if ($type == 'url')
                return [];

            if ($type == 'static-page'|| $type == 'all-static-pages')
                return StaticPage::getMenuTypeInfo($type);
        });

        Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
            if ($type == 'static-page' || $type == 'all-static-pages')
                return StaticPage::resolveMenuItem($item, $url, $theme);
        });
    }

    public static function clearCache()
    {
        $activeTheme = Theme::getActiveTheme();

        $router = new Router($activeTheme);
        $router->clearCache();

        StaticPage::clearMenuCache($activeTheme);
    }
}