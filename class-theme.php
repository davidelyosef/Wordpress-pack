<?php

/**
 * Class Theme
 *
 * @package dorzki\GNS
 */
class Custom_Theme
{
    public $slug;

    public $version;

    public $is_rtl = false;

    public $template_directory_uri = '';

    public $start_direction = 'ltr';

    public $default_css_start_direction = 'ltr';

    public $is_debug = false;

    /**
     * Setup class.
     */
    public function __construct() {
        global $theme_settings;

        $this->_init();

        add_action( 'after_setup_theme',  [$this, 'setup'] );
        add_action( 'init',               [$this, 'cpt_register'] );
        add_action( 'wp_enqueue_scripts', [ $this, 'css_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'js_assets' ] );
        add_action('acf/init',            [$this, 'acf_init'] );
        add_filter( 'upload_mimes',       [ $this, 'allow_svg_upload' ] );
        add_filter('display_post_states', [$this, 'display_post_states'], 10, 2);
        add_filter('template_include',    [$this, 'template_include_for_page_types'], 5);
        add_filter( 'wpseo_breadcrumb_links', [$this, 'breadcrumb_links_modify'], 50, 1);
        add_action( 'admin_init',          [$this, 'admin_init']);

        if(isset($theme_settings['post_featured_image_size']['width']) && isset($theme_settings['post_featured_image_size']['height'])) {
            add_filter( 'post_type_labels_post', [$this, 'post_type_labels_post'], 10, 1 );
        }
        if(isset($theme_settings['page_featured_image_size']['width']) && isset($theme_settings['page_featured_image_size']['height'])) {
            add_filter( 'post_type_labels_page', [$this, 'post_type_labels_page'], 10, 1 );
        }

        add_filter( 'use_block_editor_for_post', '__return_false', 10 );
        add_filter( 'show_admin_bar', '__return_false' );

    }


    /**
     * Initial theme valuables
     */
    private function _init() {
        global $theme_settings;

        $this->slug = $theme_settings['slug'];

        $this->version = $theme_settings['version'];

        $class_name = get_class($this);

        $this->is_rtl = is_rtl();

        $this->template_directory_uri = get_theme_file_uri();

        if( defined('WP_DEBUG') && WP_DEBUG ) {
            $this->is_debug = WP_DEBUG;
        }

        if( $this->is_rtl ) {
            $this->start_direction = 'rtl';
        }

        if( isset($theme_settings['default_css_start_direction']) ) {
            $this->default_css_start_direction = $theme_settings['default_css_start_direction'];
        }

        do_action("{$class_name}_init", $this );
    }


    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    public function setup() {
        global $theme_settings;
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         */
        load_theme_textdomain( $this->slug, get_template_directory() . '/languages' );

        foreach($theme_settings['theme_support'] as $feature => $args) {
            if(count($args) > 0) {
                add_theme_support($feature, $args);
            } else {
                add_theme_support($feature);
            }
        }

        add_post_type_support( 'page', 'excerpt' );


        foreach ( $theme_settings['image_sizes'] as $size ) {
            add_image_size(
                $size['name'],
                $size['width'],
                $size['height'],
                $size['crop']
            );
        }

        foreach ( $theme_settings['menus'] as $location => $description ) {
            register_nav_menu($location, __($description, $this->slug));
        }


        remove_action( 'wp_head', 'wp_generator' );
        remove_action( 'wp_head', 'wlwmanifest_link' );
        remove_action( 'wp_head', 'rsd_link' );
        remove_action( 'wp_head', 'wp_shortlink_wp_head' );
        remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
        remove_action( 'wp_head', 'feed_links_extra', 3 );
        remove_action( 'wp_head', 'rest_output_link_wp_head' );

        add_filter( 'the_generator', '__return_false' );

    }

    /**
     * Register theme css files according to viewed page.
     */
    public function css_assets() {
        global $theme_settings;

        foreach( $theme_settings['css_assets_to_remove'] as $handle) {
            wp_dequeue_style($handle);
            wp_deregister_style( $handle );
        }

        $path_builder = function($path, $add_start_direction, $size = false) {
            $full_path = str_replace(ABSPATH,"/", get_stylesheet_directory()) ."/" .  $path;
            if($size) {
                $full_path .= "-above-" . $size;
            }
            return $full_path . ($add_start_direction? "." . $this->start_direction : "") . ".css";
        };

        foreach( $theme_settings['css_assets'] as $handle => $css) {

            if(
                !isset($css['condition']) ||
                (is_callable($css['condition']) && $css['condition']()) ||
                (!is_callable($css['condition']) && $css['condition'])
            ) {

                $add_start_direction = !($this->start_direction == $this->default_css_start_direction || (isset($css['ignore_direction']) && $css['ignore_direction']));
                $path = $css['path'];
                if (!isset(parse_url($path)['host'])) {
                    $path = $path_builder( $css['path'], $add_start_direction);
                }

                $deps = array_map(function($dep) { return $this->slug . '-' . $dep; }, $css['deps']);

                wp_enqueue_style( $this->slug . '-' . $handle, $path, $deps, $this->version );

                if(isset($css['sizes'])) {
                    foreach ($css['sizes'] as $size) {
                        $file_path = $path_builder( $css['path'], $add_start_direction, $size );

                        if(file_exists(ABSPATH.$file_path)) {
                            wp_enqueue_style( $this->slug . '-' . $handle . "-above-" . $size, $file_path, $deps, $this->version, "(min-width: {$size}px)" );
                        } else {
                            wp_add_inline_style($this->slug . '-' . $handle, "/* import ".$this->slug . '-' . $handle . "-above-" . $size." - ".$file_path ." - file not found */ ");
                        }
                    }
                }
            }

        }

    }

    /**
     * Register theme js files.
     */
    public function js_assets() {
        global $theme_settings;

        foreach( $theme_settings['js_assets_to_remove'] as $handle) {
            wp_dequeue_script($handle);
            wp_deregister_script( $handle );
        }
        $removedSlugs = [];
        foreach( $theme_settings['js_assets'] as $handle => $js) {

            if(
                !isset($js['condition']) ||
                (is_callable($js['condition']) && $js['condition']()) ||
                (!is_callable($js['condition']) && $js['condition'])
            ) {

                $path = $js['path'];
                if (!isset(parse_url($path)['host'])) {
                    $path = $this->template_directory_uri ."/" .  $path . ".js";
                }

                $deps = array_map(function($dep) use($removedSlugs) {
                    return !in_array($dep, $removedSlugs) ? $this->slug . '-' . $dep : $dep;

                }, $js['deps']);

                if(isset($js['removeSlug']) && $js['removeSlug']) {
                    wp_enqueue_script( $handle, $path, $deps, $this->version, $js['in_footer'] );
                    $removedSlugs[] = $handle;
                } else {
                    wp_enqueue_script(  $this->slug . '-'  . $handle, $path, $deps, $this->version, $js['in_footer'] );
                }

                if(isset($js['localize_script'])) {

                    foreach($js['localize_script'] as $objectName => $scripts) {

                        $objectData = [];

                        foreach( $scripts as $script_handle => $js) {
                            if(
                                !isset($js['condition']) ||
                                (is_callable($js['condition']) && $js['condition']()) ||
                                (!is_callable($js['condition']) && $js['condition'])
                            ) {
                                $objectData[$script_handle] = $js['data']();
                            }
                        }

                        wp_localize_script(
                            $this->slug . '-' . $handle,
                            $objectName,
                            $objectData
                        );
                    }
                }
            }
        }
    }

    public function acf_init() {
        global $theme_settings;

        if( function_exists('acf_add_local_field_group') ) {

            acf_add_local_field_group(array(
                'key'      => $this->slug . '_group_options',
                'title'    => 'Options Group (Created Programmatically)',
                'label_placement' => 'left',
                'location' => array(
                    array(
                        array(
                            'param'    => 'options_page',
                            'operator' => '==',
                            'value'    => 'acf-options',
                        ),
                    ),
                ),
            ));

        }

        if( function_exists('acf_add_options_page') ) {
            acf_add_options_page();

            new Custom_ACF_Page_Type(array_map(function($page_type) { return $page_type['name']; }, $theme_settings['page_types']), $this->slug . '_group_options');

        }
    }

    /**
     * Allow to upload SVG files.
     *
     * @param array $mimes list of mime types.
     *
     * @return mixed
     */
    public function allow_svg_upload( $mimes ) {

        $mimes['svg'] = 'image/svg+xml';

        return $mimes;

    }

    public function cpt_register(  ) {
        global $theme_settings;

        foreach ($theme_settings['cpts'] as $post_type => $cpt) {

            $labels = array(
                'name'                  => _x($cpt['plural_name'], 'cpt', $this->slug),
                'singular_name'         => _x($cpt['singular_name'], 'cpt', $this->slug),
                'menu_name'             => _x($cpt['plural_name'], 'cpt', $this->slug),
                'name_admin_bar'        => _x($cpt['singular_name'], 'cpt', $this->slug),
                'all_items'             => _x('All ' . $cpt['plural_name'], 'cpt', $this->slug),
                'add_new_item'          => _x('Add new ' . $cpt['singular_name'], 'cpt', $this->slug),
                'add_new'               => _x('Add ' . $cpt['singular_name'], 'cpt', $this->slug),
                'new_item'              => _x('New ' . $cpt['singular_name'], 'cpt', $this->slug),
                'edit_item'             => _x('Edit ' . $cpt['singular_name'], 'cpt', $this->slug),
                'update_item'           => _x('Update ' . $cpt['singular_name'], 'cpt', $this->slug),
                'view_item'             => _x('View ' . $cpt['singular_name'], 'cpt', $this->slug),
                'view_items'            => _x('View ' . $cpt['singular_name'], 'cpt', $this->slug),
                'search_items'          => _x('Search ' . $cpt['plural_name'], 'cpt', $this->slug),
                'not_found'             => _x($cpt['plural_name'] . ' not found', 'cpt', $this->slug)
            );

            if(isset($cpt['featured_image_size']['width']) && isset($cpt['featured_image_size']['height'])) {
                $labels['featured_image'] = sprintf(_x('Featured Image - Size: %dpx X %dpx', 'cpt', $this->slug), $cpt['featured_image_size']['width'], $cpt['featured_image_size']['height']);
            }

            register_post_type( $post_type, array_merge(array(
                'label'                 => _x($cpt['plural_name'], 'cpt', $this->slug),
                'labels'                => $labels,
                'public'                => true,
                'show_ui'               => true,
                'show_in_nav_menus'     => true,
                'show_in_menu'          => true,
                'menu_icon'             => $cpt['dashicon'] ?? 'dashicons-awards',
                'query_var'             => true,
                'has_archive'           => false,
                'hierarchical'          => false,
                'menu_position'         => 10,
                'capability_type'     => 'page',
                'supports'              => array( 'title', 'thumbnail', 'excerpt', 'editor'  )
            ), $cpt['args']) );

            if( isset($cpt['taxonomies']) ) {
                foreach($cpt['taxonomies'] as $taxonomy_handle => $taxonomy) {

                    register_taxonomy( $taxonomy_handle, $post_type, array_merge([
                        'labels'            => [
                            'name'          => _x($taxonomy['plural_name'], 'cpt', $this->slug),
                            'singular_name' => _x($taxonomy['singular_name'], 'cpt', $this->slug),
                        ],
                        'public'            => true,
                        'hierarchical'      => true,
                        'show_admin_column' => true,
                    ], $taxonomy['args']) );
                }
            }
        }

    }

    public function display_post_states( $post_states, $post ) {
        global $theme_settings;
        if( $theme_settings['page_types'] ) {
            foreach( $theme_settings['page_types'] as $key => $choice ) {
                if( get_field($key, 'option') == $post->ID && !array_key_exists($key,$post_states) ) {
                    $post_states[$key] = $choice['name'];
                }
            }
        }
        return $post_states;
    }

    public function template_include_for_page_types($template) {
        global $theme_settings;
        if( $theme_settings['page_types'] ) {

            $page_id = get_queried_object_id();

            $page_compare = function ($field_key) use ($page_id) {
                return is_page($page_id) && layer_get_page_type_id($field_key) == $page_id;
            };

            foreach( $theme_settings['page_types'] as $key => $choice ) {
                if($page_compare($key)) {
                    $new_template = locate_template([$choice['template']]);
                    if (!empty($new_template)) {
                        $template = $new_template;
                        break;
                    }
                }
            }
            return $template;
        }
    }

    public function post_type_labels_post($labels) {
        global $theme_settings;

        $labels->featured_image = sprintf(_x('Featured Image - Size: %dpx X %dpx', 'cpt', $this->slug), $theme_settings['post_featured_image_size']['width'], $theme_settings['post_featured_image_size']['height']);
        return $labels;
    }
    public function post_type_labels_page($labels) {
        global $theme_settings;

        $labels->featured_image = sprintf(_x('Featured Image - Size: %dpx X %dpx', 'cpt', $this->slug), $theme_settings['page_featured_image_size']['width'], $theme_settings['page_featured_image_size']['height']);
        return $labels;
    }

    public function breadcrumb_links_modify($crumb) {
        global $theme_settings;
        if( $theme_settings['breadcrumbs'] ) {

            $modify_crumb = new Modify_Crumb($crumb, false);

            $queried_object = get_queried_object();

            if (!$queried_object) return $crumb;

            // Get the entity ID
            $queried_object_id = isset($queried_object->ID) ? $queried_object->ID : $queried_object->term_id;

            foreach ($theme_settings['breadcrumbs'] as $cpt => $breadcrumb) {
                if (is_singular($cpt)) {
                    if(isset($breadcrumb['page_type'])) {
                        $page_type_id = layer_get_page_type_id($breadcrumb['page_type']);
                        if($page_type_id) {
                            $modify_crumb->push_entity_array(array(
                                'text' => get_the_title($page_type_id),
                                'url' => get_the_permalink($page_type_id),
                                'allow_html' => true
                            ), $breadcrumb['position']);
                        }
                    } else {
                        $modify_crumb->push_entity_array(array(
                            'text' => $breadcrumb['title'],
                            'allow_html' => true
                        ), $breadcrumb['position']);
                    }

                    $crumb = $modify_crumb->get_crumb();
                    break;
                }
            }

            return $crumb;
        }

    }

    public function admin_init() {

        //Add add to gravity form for editor
        $role = get_role('editor');
        $role->add_cap('gform_full_access');
    }
}

new Custom_Theme();