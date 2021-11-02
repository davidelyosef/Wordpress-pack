<?php

function is_acf_exists()
{
    return class_exists('ACF');
}

function get_field($selector, $post_id = false, $format_value = true)
{
    return is_acf_exists() ? get_field($selector, $post_id, $format_value) : null;;
}

function trim_recursive(&$o, $character_mask = null)
{

    // Only apply trim() to strings
    if (is_string($o)) {
        // Respect the $character_mask; cannot pass null as 2nd parameter for some HHVM versions
        $o = trim($o, $character_mask ?? " \t\n\r\0\x0B");
        return $o;
    }

    if (is_iterable($o) || is_object($o)) {
        // Loop this array/object and apply trim_r() to its members
        foreach ($o as &$v) {
            trim_recursive($v);
        }
    }

    // Supply this just in case the invoker wishes to receive result as a reference
    return $o;
}

function get_theme_option($selector, $format_value = true)
{
    return get_field($selector, 'options', $format_value);
}

/**
 * @param string $page_type
 * @return mixed
 */
function get_page_type_id($page_type) {
    return get_field($page_type, 'option');
}
/**
 * @param string $page_type
 * @return bool|false|string|WP_Error
 */
function get_page_type_permalink($page_type)
{
    return get_the_permalink(get_page_type_id($page_type));
}

/**
 * @param array $args
 * @return bool|int[]|WP_Post[]
 * You can use: add_filter('layer/get_posts/apply_on_global_query', '__return_true'); and remove_filter('layer/get_posts/apply_on_global_query', '__return_true');
 */
function get_posts($args) {
    global $wp_query;
    $apply_on_global_query = apply_filters('layer/get_posts/apply_on_global_query', false);
    $q = new WP_Query($args);
    if($apply_on_global_query) {
        $wp_query = $q;
    }
    if ($q->have_posts()) {
        return $q->posts;
    } else {
        return false;
    }
}

/**
 * @param string[]|string $post_types
 * @return bool|int[]|WP_Post[]
 */
function get_all_posts_by_post_types($post_types)
{
    return get_limited_number_of_posts_by_post_types($post_types, -1);
}

/**
 * @param string[]|string $post_types
 * @param int $limit
 * @return bool|int[]|WP_Post[]
 */
function get_limited_number_of_posts_by_post_types($post_types, $limit)
{
    return get_posts([
        'post_type' => is_array($post_types) ? $post_types : [$post_types],
        'posts_per_page' => $limit
    ]);
}

/**
 * @param string[]|string $post_types
 * @param int $limit
 * @return bool|int[]|WP_Post[]
 */
function get_limited_number_of_paged_posts_by_post_types($post_types, $limit)
{
    return get_posts([
        'post_type' => is_array($post_types) ? $post_types : [$post_types],
        'posts_per_page' => $limit,
        'paged' => get_query_var('paged', 1)
    ]);
}

/**
 * @param int|string $category
 * @return bool|int[]|WP_Post[]
 */
function get_all_posts_by_category_and_paged($category) {
    return get_limited_number_of_posts_by_category_and_paged($category, -1);
}

/**
 * @param int|string $category
 * @param int $limit
 * @return bool|int[]|WP_Post[]
 */
function get_limited_number_of_posts_by_category_and_paged($category, $limit) {
    $args = [
        'post_type' => 'post',
        'paged' => get_query_var('paged', 1),
        'posts_per_page' => $limit
    ];
    if(is_string($category)) {
        $args['category_name'] = $category;
    } else {
        $args['cat'] = $category;
    }

    return get_posts($args);
}

/**
 * @param string $page_type
 * @return WP_Post
 */
function get_post_of_page_type($page_type) {
    return get_post(get_page_type_id($page_type));
}

/**
 * @param string $page_type
 * @return bool
 */
function is_page_type($page_type) {
    global $post;
    return get_page_type_id($page_type) == $post->ID;
}

/**
 * @param string[]|string $post_types
 * @return bool|int[]|WP_Post[]
 */
function get_paged_posts_type($post_types) {
    $args = [
        'post_type' => is_array($post_types) ? $post_types : [$post_types],
        'paged' => get_query_var('paged', 1)
    ];

    return get_posts($args);
}

$images_path = get_template_directory_uri() . '/assets/images';

// Debugging

function pr($arr)
{
    echo '<pre style="background: #b2b2b2; width: 100%; padding: 40px 10px; direction: ltr; margin: 0;">';
    print_r($arr);
    echo '</pre>';
}

function vd($obj)
{
    echo '<pre style="background: #ddd; width: 100%; padding: 40px 10px; direction: ltr; margin: 0;">';
    var_dump($obj);
    echo '</pre>';
}

function get_picture_tag( $src, array $source = [], $alt = '', array $classes = [] ) { ?>
    <picture>
        <?php if( $source ) : ?>
            <?php foreach ( $source as $media => $srcset ) : ?>
                <source <?php if( $media ) : ?> media="<?php echo $media ?>" <?php endif; ?> srcset="<?php echo $srcset; ?>">
            <?php endforeach; ?>
        <?php endif; ?>
        <img src="<?php echo $src; ?>"
             loading="lazy"
             alt="<?php echo $alt; ?>"
             class="<?php echo implode(" ", $classes); ?>">
    </picture>
<?php }
