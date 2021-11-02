<?php
$theme_settings = [
    'slug' => 'layer',
    'version' => '1.0.0',
    'theme_support' => [
        'post-thumbnails' => ['post', 'page', 'city'],
        'html5' => [],
        'title-tag' => []
    ],
    'post_featured_image_size' => ['width' => 800, 'height' => 400], // (optional)
    'page_featured_image_size' => ['width' => 800, 'height' => 400], // (optional)
    'image_sizes' => [
        ['name' => 'Size 1 Example', 'width' => 100, 'height' => 100, 'crop' => false ],
        ['name' => 'Size 2 Example', 'width' => 200, 'height' => 200, 'crop' => false ],
        ['name' => 'Size 3 Example', 'width' => 300, 'height' => 300, 'crop' => false ],
    ],
    'menus' => [
        'example1' => 'Example Menu 1',
        'example2' => 'Example Menu 2',
    ],
    'default_css_start_direction' => 'ltr',
    'css_assets_to_remove' => ['wp-block-library', 'admin-bar'],
    'css_assets' => [
        /*
         * array structure:
         * HANDLE => [
         *      'path'              => FULL URL or relative path (without js extension)
         *      'deps'              => array of asset dependencies
         *      'condition'         => true / false / function that returns true / false
         *      'sizes'             => array of required media sized (min-width)
         *      'ignore_direction'  => boolean, default 'false' (optional). when true - will not add .rtl for css files on RTL enqueue
         * ]
         */
        'google-fonts' => ['path' => '//fonts.googleapis.com/css2?family=Assistant:wght@400;700&family=Miriam+Libre:wght@400;700&display=swap', 'deps' => []],
        'example' => ['path' => 'assets/css/example', 'deps' => ['google-fonts'], 'condition' => function() { return true; }, 'sizes' => ['768', '992', '1000', '1200', '1600', '1800']],
        'example-2' => ['path' => 'assets/css/example-2', 'deps' => [], 'condition' => true, 'sizes' => ['768', '992', '1200', '1600'], 'ignore_direction' => true]
    ],
    'js_assets_to_remove' => ['jquery', 'admin-bar', 'wp-embed', 'comment-reply'],
    'js_assets' => [
        /*
         * array structure:
         * HANDLE => [
         *      'path'            => FULL URL or relative path (without js extension)
         *      'deps'            => array of asset dependencies
         *      'in_footer'       => true / false
         *      'removeSlug'      => true / false (optional)
         *      'condition'       => true / false / function that returns true / false
         *      'localize_script' => [    (optional)
         *          OBJECT_NAME => [
         *              HANDLE => [
         *                  'data'      => Anonymous function that returns the required data
         *                  'condition' => true / false / function that returns true / false
         *              ]
         *          ]
         *      ]
         * ]
         */
        'jquery' => ['path' => 'https://code.jquery.com/jquery-3.6.0.min.js', 'deps' => [], 'in_footer' => false, 'removeSlug' => true],
        'example' => ['path' => 'assets/js/example', 'deps' => ['jquery'], 'in_footer' => true, 'condition' => true, 'localize_script' => [
            'siteData' => [
                'homeUrl' => ['data' => function() { return get_home_url(); }, 'condition' => true]
            ]
        ]],
        'modal' => ['path' => 'assets/js/modal', 'deps' => ['jquery'], 'in_footer' => true, 'condition' => true],
    ],
    'cpts' => [
        /*
         * array structure:
         * POST TYPE => [
         *      'singular_name' => CPT SINGULAR NAME
         *      'plural_name'   => CPT PLURAL NAME
         *      'featured_image_size' => ['width' => INT, 'height' => INT]      (optional)
         *      'args'          => Array of args parameter based on https://developer.wordpress.org/reference/functions/register_post_type/
         *      'dashicon'      => Dashboard icon of the cpt      (optional)
         *      'taxonomies'    => [    (optional)
         *          TAXONOMY => [
         *              'singular_name' => TAXONOMY SINGULAR NAME
         *              'plural_name'   => TAXONOMY PLURAL NAME
         *              'args'          => Array of args parameter based on https://developer.wordpress.org/reference/functions/register_taxonomy/
         *          ]
         *      ]
         * ]
         */
        'city' => ['singular_name' => 'City', 'plural_name' => 'Cities', 'featured_image_size' => ['width' => 100, 'height' => 500], 'args' => [], 'taxonomies' => [
            'city_cat' =>  ['singular_name' => 'City Category', 'plural_name' => 'Cities Categories', 'args' => []]
        ]]
    ],
    'page_types' => [
        'page_for_404' => ['name' => 'Page For 404', 'template' => 'pagetype-404.php'],
        'page_for_posts' => ['name' => 'Page For Posts', 'template' => 'pagetype-posts.php'],
    ],
    'breadcrumbs' => [
        /*
         * array structure:
         * POST TYPE => [
         *      'page_type' =>  Slug of page type (optional, required for link)
         *      'title'   =>    Title of the crumb (optional - only when page_type is not apply)
         *      'position' =>   Position to add the new crumb
         * ]
         */
        'city' => ['page_type' => 'page_for_posts', 'title' => 'Posts', 'position' => 1]
    ]
];
