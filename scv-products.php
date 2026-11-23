<?php
/**
 * Plugin Name: SCV Products
 * Description: Custom Product post type, Categories and Collections taxonomies, and Always Available product cards block for SoCal VoCals Swag.
 * Version: 1.1.2
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: SoCal VoCals Swag
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SCV_PRODUCTS_VER', '1.1.2');
define('SCV_PRODUCTS_PATH', plugin_dir_path(__FILE__));
define('SCV_PRODUCTS_URL', plugin_dir_url(__FILE__));
define(
    'SCV_PRODUCTS_UPDATE_MANIFEST_URL',
    'https://github.com/recordandmix/scv-products/releases/latest/download/update-manifest.json'
);

require_once SCV_PRODUCTS_PATH . 'includes/updater.php';

/* --------------------------------------------------------------------------
 * Post type, taxonomy, and REST-exposed meta
 * -------------------------------------------------------------------------- */

function scv_products_register_types() {
    register_taxonomy('scv_collection', ['scv_product'], [
        'labels' => [
            'name'              => 'Collections',
            'singular_name'     => 'Collection',
            'search_items'      => 'Search Collections',
            'all_items'         => 'All Collections',
            'edit_item'         => 'Edit Collection',
            'update_item'       => 'Update Collection',
            'add_new_item'      => 'Add New Collection',
            'new_item_name'     => 'New Collection Name',
            'menu_name'         => 'Collections',
        ],
        'hierarchical'       => true,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_nav_menus'  => false,
        'show_tagcloud'      => false,
        'rewrite'            => false,
        'show_in_rest'       => true,
        'rest_base'          => 'scv-collections',
    ]);

    register_term_meta('scv_collection', '_scv_collection_order', [
        'type'              => 'integer',
        'single'            => true,
        'default'           => 10,
        'sanitize_callback' => 'scv_products_sanitize_int',
        'auth_callback'     => function () { return current_user_can('manage_categories'); },
        'show_in_rest'      => true,
    ]);

    register_taxonomy('scv_category', ['scv_product'], [
        'labels' => [
            'name'              => 'Categories',
            'singular_name'     => 'Category',
            'search_items'      => 'Search Categories',
            'all_items'         => 'All Categories',
            'edit_item'         => 'Edit Category',
            'update_item'       => 'Update Category',
            'add_new_item'      => 'Add New Category',
            'new_item_name'     => 'New Category Name',
            'menu_name'         => 'Categories',
        ],
        'hierarchical'       => true,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_nav_menus'  => false,
        'show_tagcloud'      => false,
        'rewrite'            => false,
        'show_in_rest'       => true,
        'rest_base'          => 'scv-categories',
    ]);

    register_term_meta('scv_category', '_scv_category_order', [
        'type'              => 'integer',
        'single'            => true,
        'default'           => 10,
        'sanitize_callback' => 'scv_products_sanitize_int',
        'auth_callback'     => function () { return current_user_can('manage_categories'); },
        'show_in_rest'      => true,
    ]);

    register_post_type('scv_product', [
        'labels' => [
            'name'               => 'Products',
            'singular_name'      => 'Product',
            'menu_name'          => 'Products',
            'name_admin_bar'     => 'Product',
            'add_new'            => 'Add Product',
            'add_new_item'       => 'Add New Product',
            'new_item'           => 'New Product',
            'edit_item'          => 'Edit Product',
            'view_item'          => 'View Product',
            'all_items'          => 'All Products',
            'search_items'       => 'Search Products',
            'not_found'          => 'No products found.',
            'not_found_in_trash' => 'No products found in Trash.',
        ],
        'public'              => false,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'has_archive'         => false,
        'rewrite'             => false,
        'query_var'           => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => false,
        'menu_position'       => 25,
        'menu_icon'           => 'dashicons-store',
        'supports'            => ['title', 'custom-fields'],
        'taxonomies'          => ['scv_collection', 'scv_category'],
        'show_in_rest'        => true,
        'rest_base'           => 'scv-products',
    ]);

    $auth_edit = function () { return current_user_can('edit_posts'); };

    register_post_meta('scv_product', '_scv_garment', [
        'type'              => 'string',
        'single'            => true,
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => $auth_edit,
        'show_in_rest'      => true,
    ]);

    register_post_meta('scv_product', '_scv_provider', [
        'type'              => 'string',
        'single'            => true,
        'default'           => 'Zazzle',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => $auth_edit,
        'show_in_rest'      => true,
    ]);

    register_post_meta('scv_product', '_scv_short_desc', [
        'type'              => 'string',
        'single'            => true,
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
        'auth_callback'     => $auth_edit,
        'show_in_rest'      => true,
    ]);

    register_post_meta('scv_product', '_scv_sort_order', [
        'type'              => 'integer',
        'single'            => true,
        'default'           => 10,
        'sanitize_callback' => 'scv_products_sanitize_int',
        'auth_callback'     => $auth_edit,
        'show_in_rest'      => true,
    ]);

    register_post_meta('scv_product', '_scv_hide_swatches', [
        'type'              => 'boolean',
        'single'            => true,
        'default'           => false,
        'sanitize_callback' => 'rest_sanitize_boolean',
        'auth_callback'     => $auth_edit,
        'show_in_rest'      => true,
    ]);

    register_post_meta('scv_product', '_scv_swatch_aria_label', [
        'type'              => 'string',
        'single'            => true,
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => $auth_edit,
        'show_in_rest'      => true,
    ]);

    register_post_meta('scv_product', '_scv_default_color', [
        'type'              => 'string',
        'single'            => true,
        'default'           => '',
        'sanitize_callback' => 'sanitize_key',
        'auth_callback'     => $auth_edit,
        'show_in_rest'      => true,
    ]);

    register_post_meta('scv_product', '_scv_colors', [
        'type'              => 'array',
        'single'            => true,
        'default'           => [],
        'sanitize_callback' => 'scv_products_sanitize_colors',
        'auth_callback'     => $auth_edit,
        'show_in_rest'      => [
            'schema' => [
                'type'  => 'array',
                'items' => [
                    'type'       => 'object',
                    'properties' => [
                        'key'      => ['type' => 'string'],
                        'label'    => ['type' => 'string'],
                        'swatch'   => ['type' => 'string'],
                        'url'      => ['type' => 'string'],
                        'photos'   => [
                            'type'  => 'array',
                            'items' => ['type' => 'integer'],
                        ],
                        'status'   => [
                            'type' => 'string',
                            'enum' => ['live', 'out_of_stock', 'coming_soon'],
                        ],
                        'order_by' => ['type' => 'string'],
                        'provider' => ['type' => 'string'],
                    ],
                ],
            ],
        ],
    ]);
}
add_action('init', 'scv_products_register_types');

function scv_products_sanitize_int($value) {
    // WordPress passes 4 args to sanitize_callback via apply_filters; intval() is an
    // internal function and fatals on PHP 8+ if given more than 2 arguments.
    return (int) $value;
}

function scv_products_sanitize_hex($hex) {
    $hex = trim((string) $hex);
    if ($hex === '') {
        return '';
    }
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $hex) || preg_match('/^#[0-9a-fA-F]{3}$/', $hex)) {
        return strtolower($hex);
    }
    return '';
}

function scv_products_sanitize_colors($raw) {
    if (!is_array($raw)) {
        return [];
    }
    $clean = [];
    $used_keys = [];
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $label = sanitize_text_field($row['label'] ?? '');
        $key = sanitize_title($row['key'] ?? '');
        if ($key === '') {
            $key = $label !== '' ? sanitize_title($label) : 'default';
        }
        $base_key = $key;
        $n = 2;
        while (isset($used_keys[$key])) {
            $key = $base_key . '-' . $n;
            $n++;
        }
        $used_keys[$key] = true;

        $status = sanitize_key($row['status'] ?? 'live');
        if ($status === 'available') {
            $status = 'live';
        }
        if (!in_array($status, ['live', 'out_of_stock', 'coming_soon'], true)) {
            $status = 'live';
        }

        $photos_raw = $row['photos'] ?? [];
        if (is_string($photos_raw)) {
            $photos_raw = array_filter(array_map('trim', explode(',', $photos_raw)));
        }
        $photos = [];
        if (is_array($photos_raw)) {
            foreach ($photos_raw as $pid) {
                $pid = (int) $pid;
                if ($pid > 0 && !in_array($pid, $photos, true)) {
                    $photos[] = $pid;
                }
            }
        }

        $swatch = scv_products_sanitize_hex($row['swatch'] ?? '');
        $url = esc_url_raw(trim((string) ($row['url'] ?? '')));
        $order_by = sanitize_text_field($row['order_by'] ?? '');
        $row_provider = sanitize_text_field($row['provider'] ?? '');

        if ($label === '' && $url === '' && empty($photos)) {
            continue;
        }

        $clean[] = [
            'key'      => $key,
            'label'    => $label,
            'swatch'   => $swatch,
            'url'      => $url,
            'photos'   => $photos,
            'status'   => $status,
            'order_by' => $order_by,
            'provider' => $row_provider,
        ];
    }
    return $clean;
}

/* --------------------------------------------------------------------------
 * Collection order field in wp-admin
 * -------------------------------------------------------------------------- */

add_action('scv_collection_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="scv-collection-order">Display Order</label>
        <input type="number" name="scv_collection_order" id="scv-collection-order" value="10" step="1" style="width: 100px;">
        <p class="description">Lower numbers appear first on the page (e.g. Classic Collection = 10).</p>
    </div>
    <?php
});

add_action('scv_collection_edit_form_fields', function ($term) {
    $order = (int) get_term_meta($term->term_id, '_scv_collection_order', true);
    if ($order === 0 && !metadata_exists('term', $term->term_id, '_scv_collection_order')) {
        $order = 10;
    }
    ?>
    <tr class="form-field">
        <th scope="row"><label for="scv-collection-order">Display Order</label></th>
        <td>
            <input type="number" name="scv_collection_order" id="scv-collection-order" value="<?php echo esc_attr($order); ?>" step="1" style="width: 100px;">
            <p class="description">Lower numbers appear first on the page.</p>
        </td>
    </tr>
    <?php
});

function scv_products_save_collection_order($term_id) {
    if (!current_user_can('manage_categories')) {
        return;
    }
    if (isset($_POST['scv_collection_order'])) {
        update_term_meta($term_id, '_scv_collection_order', (int) $_POST['scv_collection_order']);
    }
}
add_action('created_scv_collection', 'scv_products_save_collection_order');
add_action('edited_scv_collection', 'scv_products_save_collection_order');

add_filter('manage_edit-scv_collection_columns', function ($cols) {
    $cols['scv_order'] = 'Order';
    return $cols;
});
add_filter('manage_scv_collection_custom_column', function ($out, $col, $term_id) {
    if ($col === 'scv_order') {
        $val = get_term_meta($term_id, '_scv_collection_order', true);
        return $val === '' ? '10' : esc_html((string) (int) $val);
    }
    return $out;
}, 10, 3);

/* --------------------------------------------------------------------------
 * Category order field in wp-admin
 * -------------------------------------------------------------------------- */

add_action('scv_category_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="scv-category-order">Display Order</label>
        <input type="number" name="scv_category_order" id="scv-category-order" value="10" step="1" style="width: 100px;">
        <p class="description">Lower numbers appear first on the page (e.g. Sweatshirts &amp; Hoodies = 10, Hats = 20).</p>
    </div>
    <?php
});

add_action('scv_category_edit_form_fields', function ($term) {
    $order = (int) get_term_meta($term->term_id, '_scv_category_order', true);
    if ($order === 0 && !metadata_exists('term', $term->term_id, '_scv_category_order')) {
        $order = 10;
    }
    ?>
    <tr class="form-field">
        <th scope="row"><label for="scv-category-order">Display Order</label></th>
        <td>
            <input type="number" name="scv_category_order" id="scv-category-order" value="<?php echo esc_attr($order); ?>" step="1" style="width: 100px;">
            <p class="description">Lower numbers appear first on the page.</p>
        </td>
    </tr>
    <?php
});

function scv_products_save_category_order($term_id) {
    if (!current_user_can('manage_categories')) {
        return;
    }
    if (isset($_POST['scv_category_order'])) {
        update_term_meta($term_id, '_scv_category_order', (int) $_POST['scv_category_order']);
    }
}
add_action('created_scv_category', 'scv_products_save_category_order');
add_action('edited_scv_category', 'scv_products_save_category_order');

add_filter('manage_edit-scv_category_columns', function ($cols) {
    $cols['scv_order'] = 'Order';
    return $cols;
});
add_filter('manage_scv_category_custom_column', function ($out, $col, $term_id) {
    if ($col === 'scv_order') {
        $val = get_term_meta($term_id, '_scv_category_order', true);
        return $val === '' ? '10' : esc_html((string) (int) $val);
    }
    return $out;
}, 10, 3);

/* --------------------------------------------------------------------------
 * wp-admin Product Meta Boxes & List Columns
 * -------------------------------------------------------------------------- */

add_action('admin_enqueue_scripts', function ($hook) {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->post_type !== 'scv_product') {
        return;
    }
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style('scv-products-admin', SCV_PRODUCTS_URL . 'assets/admin.css', [], SCV_PRODUCTS_VER);
    wp_enqueue_script('scv-products-admin', SCV_PRODUCTS_URL . 'assets/admin.js', ['jquery'], SCV_PRODUCTS_VER, true);
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'scv_product_details',
        'Product Details',
        'scv_products_render_details_box',
        'scv_product',
        'normal',
        'high'
    );
    add_meta_box(
        'scv_product_colors',
        'Colors, Photos & Links',
        'scv_products_render_colors_box',
        'scv_product',
        'normal',
        'high'
    );
});

function scv_products_render_details_box($post) {
    wp_nonce_field('scv_product_save', 'scv_product_nonce');
    $garment = get_post_meta($post->ID, '_scv_garment', true);
    $provider = get_post_meta($post->ID, '_scv_provider', true);
    if ($provider === '') {
        $provider = 'Zazzle';
    }
    $short_desc = get_post_meta($post->ID, '_scv_short_desc', true);
    $sort_order = get_post_meta($post->ID, '_scv_sort_order', true);
    if ($sort_order === '') {
        $sort_order = 10;
    }
    $hide_swatches = (bool) get_post_meta($post->ID, '_scv_hide_swatches', true);
    $aria_label = get_post_meta($post->ID, '_scv_swatch_aria_label', true);
    ?>
    <div class="scv-admin-grid">
        <div class="scv-admin-field">
            <label for="scv-garment">Garment / Category</label>
            <input type="text" id="scv-garment" name="scv_garment" list="scv-garment-list" value="<?php echo esc_attr($garment); ?>" placeholder="e.g. Hoodies &amp; Sweatshirts">
            <datalist id="scv-garment-list">
                <option value="Hoodies &amp; Sweatshirts"></option>
                <option value="T-Shirts"></option>
                <option value="Hats"></option>
                <option value="Accessories"></option>
            </datalist>
            <p class="scv-admin-help">Shown as the small label above the card title.</p>
        </div>
        <div class="scv-admin-field">
            <label for="scv-provider">Provider</label>
            <input type="text" id="scv-provider" name="scv_provider" list="scv-provider-list" value="<?php echo esc_attr($provider); ?>" placeholder="Zazzle">
            <datalist id="scv-provider-list">
                <option value="Zazzle"></option>
                <option value="LogoSportswear"></option>
                <option value="Printful"></option>
                <option value="CustomInk"></option>
            </datalist>
            <p class="scv-admin-help">Used in the button text, e.g. <em>Shop on Zazzle</em>. A color can override this.</p>
        </div>
        <div class="scv-admin-field scv-field-full">
            <label for="scv-short-desc">Short Description</label>
            <textarea id="scv-short-desc" name="scv_short_desc" rows="2" placeholder="One-line description shown on the card."><?php echo esc_textarea($short_desc); ?></textarea>
            <p class="scv-admin-help">Site rule: do not include prices on Always Available cards.</p>
        </div>
        <div class="scv-admin-field">
            <label for="scv-sort-order">Sort Order</label>
            <input type="number" id="scv-sort-order" name="scv_sort_order" value="<?php echo esc_attr((string) (int) $sort_order); ?>" step="1" style="width: 120px;">
            <p class="scv-admin-help">Lower numbers appear first within the collection.</p>
        </div>
        <div class="scv-admin-field">
            <label for="scv-default-color">Default color</label>
            <select id="scv-default-color" name="scv_default_color">
                <option value="">First live color</option>
                <?php
                $default_color = (string) get_post_meta($post->ID, '_scv_default_color', true);
                $default_color_rows = get_post_meta($post->ID, '_scv_colors', true);
                if (!is_array($default_color_rows)) {
                    $default_color_rows = [];
                }
                foreach ($default_color_rows as $row) {
                    $k = sanitize_key((string) ($row['key'] ?? ''));
                    if ($k === '') {
                        continue;
                    }
                    $lab = (string) ($row['label'] ?? '');
                    if ($lab === '') {
                        $lab = $k;
                    }
                    echo '<option value="' . esc_attr($k) . '"' . selected($default_color, $k, false) . '>' . esc_html($lab) . '</option>';
                }
                ?>
            </select>
            <p class="scv-admin-help">Shown first on the card (photo, label, and shop button). Swatch order is unchanged. If this color is missing, sold out, or coming soon, the first live color is used.</p>
        </div>
        <div class="scv-admin-field">
            <label for="scv-swatch-aria">Color Picker Aria Label (optional)</label>
            <input type="text" id="scv-swatch-aria" name="scv_swatch_aria_label" value="<?php echo esc_attr($aria_label); ?>" placeholder="e.g. Sweatshirt color">
            <p class="scv-admin-help">Defaults to &ldquo;[Product Title] color&rdquo; if blank.</p>
        </div>
        <div class="scv-admin-field scv-field-full">
            <label style="font-weight: 400;">
                <input type="checkbox" name="scv_hide_swatches" value="1" <?php checked($hide_swatches); ?>>
                <strong>Simple single-link card</strong> (hide color dots and show &ldquo;Shop on [Provider]&rdquo; — best for hats, phone cases, or single-image items)
            </label>
        </div>
    </div>
    <?php
}

function scv_products_render_color_row($row, $index_attr = '0') {
    $label = (string) ($row['label'] ?? '');
    $key = (string) ($row['key'] ?? '');
    $swatch = (string) ($row['swatch'] ?? '#111111');
    if ($swatch === '') {
        $swatch = '#111111';
    }
    $url = (string) ($row['url'] ?? '');
    $status = (string) ($row['status'] ?? 'live');
    $order_by = (string) ($row['order_by'] ?? '');
    $photos = array_values(array_filter(array_map('intval', (array) ($row['photos'] ?? []))));
    $chip_hex = preg_match('/^#[0-9a-fA-F]{6}$/', $swatch) ? $swatch : '#cccccc';
    $picker_val = preg_match('/^#[0-9a-fA-F]{6}$/', $swatch) ? strtolower($swatch) : '#111111';
    $title_text = $label !== '' ? $label : 'Color';
    ?>
    <div class="scv-color-row" data-index="<?php echo esc_attr($index_attr); ?>">
        <div class="scv-color-row-head">
            <div class="scv-color-row-title">
                <span class="scv-color-chip" style="background: <?php echo esc_attr($chip_hex); ?>"></span>
                <span class="scv-color-row-name"><?php echo esc_html($title_text); ?></span>
            </div>
            <div class="scv-color-row-actions">
                <button type="button" class="button button-small scv-move-up" title="Move up">&uarr;</button>
                <button type="button" class="button button-small scv-move-down" title="Move down">&darr;</button>
                <button type="button" class="button button-small button-link-delete scv-remove-color">Remove</button>
            </div>
        </div>
        <div class="scv-color-fields">
            <div class="scv-admin-field">
                <label>Color Label</label>
                <input type="text" data-name="label" value="<?php echo esc_attr($label); ?>" placeholder="e.g. Black, Ash, White">
                <p class="scv-admin-help">Use &ldquo;Ash&rdquo;, never &ldquo;Gray&rdquo;.</p>
            </div>
            <div class="scv-admin-field">
                <label>Swatch Hex</label>
                <div class="scv-hex-wrap">
                    <input type="color" class="scv-hex-picker" value="<?php echo esc_attr($picker_val); ?>" aria-label="Pick swatch color">
                    <input type="text" data-name="swatch" value="<?php echo esc_attr($row['swatch'] ?? ''); ?>" placeholder="#111111">
                </div>
            </div>
            <div class="scv-admin-field">
                <label>Status</label>
                <select data-name="status">
                    <option value="live" <?php selected($status, 'live'); ?>>Live (shoppers can buy)</option>
                    <option value="out_of_stock" <?php selected($status, 'out_of_stock'); ?>>Out of stock (shown as sold out)</option>
                    <option value="coming_soon" <?php selected($status, 'coming_soon'); ?>>Coming soon (shown as coming soon)</option>
                </select>
            </div>
            <div class="scv-admin-field">
                <label>Provider (optional)</label>
                <input type="text" data-name="provider" list="scv-color-provider-list" value="<?php echo esc_attr((string) ($row['provider'] ?? '')); ?>" placeholder="Same as product">
                <p class="scv-admin-help">If set, this color&rsquo;s button reads &ldquo;Shop on [Provider]&rdquo;.</p>
            </div>
            <div class="scv-admin-field scv-field-wide">
                <label>Product Link URL</label>
                <input type="url" data-name="url" value="<?php echo esc_attr($url); ?>" placeholder="https://www.zazzle.com/...">
            </div>
            <div class="scv-admin-field">
                <label>Order-by Date (optional preorder)</label>
                <input type="date" data-name="order_by" value="<?php echo esc_attr($order_by); ?>">
                <input type="hidden" data-name="key" value="<?php echo esc_attr($key); ?>">
            </div>
            <div class="scv-admin-field scv-field-full">
                <label>Photo(s) from Media Library</label>
                <div class="scv-photos-wrap">
                    <div class="scv-photo-thumbs">
                        <?php foreach ($photos as $pid) :
                            $thumb = wp_get_attachment_image_url($pid, 'thumbnail');
                            if (!$thumb) {
                                $thumb = wp_get_attachment_image_url($pid, 'full');
                            }
                            ?>
                            <div class="scv-photo-thumb" data-id="<?php echo (int) $pid; ?>">
                                <?php if ($thumb) : ?>
                                    <img src="<?php echo esc_url($thumb); ?>" alt="">
                                <?php endif; ?>
                                <button type="button" class="scv-photo-remove" title="Remove photo">&times;</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button scv-pick-photos">Add Photo(s)</button>
                    <button type="button" class="button scv-replace-photos">Replace Photo(s)</button>
                    <input type="hidden" data-name="photos" value="<?php echo esc_attr(implode(',', $photos)); ?>">
                </div>
            </div>
        </div>
    </div>
    <?php
}

function scv_products_render_colors_box($post) {
    $colors = get_post_meta($post->ID, '_scv_colors', true);
    if (!is_array($colors) || empty($colors)) {
        $colors = [[
            'key'      => 'black',
            'label'    => 'Black',
            'swatch'   => '#111111',
            'url'      => '',
            'photos'   => [],
            'status'   => 'live',
            'order_by' => '',
            'provider' => '',
        ]];
    }
    ?>
    <p class="scv-admin-help" style="margin-bottom: 12px;">Add one row per color (or one row for a single-option item). Live colors get a shop button. Out of stock and Coming soon colors still appear on the card, dimmed, with a Sold out or Coming soon label instead of a shop link.</p>
    <div id="scv-colors-list" class="scv-colors-list">
        <?php foreach ($colors as $i => $row) {
            scv_products_render_color_row($row, (string) $i);
        } ?>
    </div>
    <p><button type="button" id="scv-add-color" class="button button-secondary">+ Add Color</button></p>
    <script type="text/html" id="scv-color-row-template">
        <datalist id="scv-color-provider-list">
            <option value="Zazzle"></option>
            <option value="LogoSportswear"></option>
            <option value="Logosportswear"></option>
            <option value="Printful"></option>
            <option value="CustomInk"></option>
        </datalist>
        <?php scv_products_render_color_row([
            'key'      => '',
            'label'    => '',
            'swatch'   => '#111111',
            'url'      => '',
            'photos'   => [],
            'status'   => 'live',
            'order_by' => '',
            'provider' => '',
        ], '__INDEX__'); ?>
    </script>
    <?php
}

add_action('save_post_scv_product', function ($post_id) {
    if (!isset($_POST['scv_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['scv_product_nonce'])), 'scv_product_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, '_scv_garment', sanitize_text_field(wp_unslash($_POST['scv_garment'] ?? '')));
    $provider = sanitize_text_field(wp_unslash($_POST['scv_provider'] ?? ''));
    update_post_meta($post_id, '_scv_provider', $provider !== '' ? $provider : 'Zazzle');
    update_post_meta($post_id, '_scv_short_desc', sanitize_textarea_field(wp_unslash($_POST['scv_short_desc'] ?? '')));
    update_post_meta($post_id, '_scv_sort_order', (int) ($_POST['scv_sort_order'] ?? 10));
    update_post_meta($post_id, '_scv_swatch_aria_label', sanitize_text_field(wp_unslash($_POST['scv_swatch_aria_label'] ?? '')));
    update_post_meta($post_id, '_scv_hide_swatches', !empty($_POST['scv_hide_swatches']) ? true : false);
    update_post_meta($post_id, '_scv_default_color', sanitize_key(wp_unslash($_POST['scv_default_color'] ?? '')));

    $raw_colors = isset($_POST['scv_colors']) && is_array($_POST['scv_colors']) ? wp_unslash($_POST['scv_colors']) : [];
    update_post_meta($post_id, '_scv_colors', scv_products_sanitize_colors($raw_colors));
});

add_filter('manage_scv_product_posts_columns', function ($cols) {
    $new = [];
    foreach ($cols as $k => $v) {
        $new[$k] = $v;
        if ($k === 'title') {
            $new['scv_garment'] = 'Garment';
            $new['scv_provider'] = 'Provider';
            $new['scv_colors'] = 'Colors';
            $new['scv_order'] = 'Order';
        }
    }
    return $new;
});

add_action('manage_scv_product_posts_custom_column', function ($col, $post_id) {
    if ($col === 'scv_garment') {
        echo esc_html((string) get_post_meta($post_id, '_scv_garment', true));
    } elseif ($col === 'scv_provider') {
        $p = (string) get_post_meta($post_id, '_scv_provider', true);
        echo esc_html($p !== '' ? $p : 'Zazzle');
    } elseif ($col === 'scv_colors') {
        $colors = get_post_meta($post_id, '_scv_colors', true);
        if (!is_array($colors) || empty($colors)) {
            echo '&mdash;';
            return;
        }
        $live = [];
        $oos = 0;
        $soon = 0;
        foreach ($colors as $c) {
            $st = $c['status'] ?? 'live';
            if ($st === 'live' || $st === 'available') {
                $live[] = ($c['label'] ?? '') !== '' ? $c['label'] : 'Default';
            } elseif ($st === 'coming_soon') {
                $soon++;
            } else {
                $oos++;
            }
        }
        $txt = !empty($live) ? implode(', ', $live) : 'None live';
        if ($oos > 0) {
            $txt .= ' (+' . $oos . ' sold out)';
        }
        if ($soon > 0) {
            $txt .= ' (+' . $soon . ' coming soon)';
        }
        echo esc_html($txt);
    } elseif ($col === 'scv_order') {
        $o = get_post_meta($post_id, '_scv_sort_order', true);
        echo esc_html($o === '' ? '10' : (string) (int) $o);
    }
}, 10, 2);

/* --------------------------------------------------------------------------
 * Frontend rendering (shared by scv/products block & [scv_products] shortcode)
 * -------------------------------------------------------------------------- */

function scv_products_register_assets() {
    wp_register_style('scv-products-front', SCV_PRODUCTS_URL . 'assets/products.css', [], SCV_PRODUCTS_VER);
    wp_register_script('scv-products-front', SCV_PRODUCTS_URL . 'assets/products.js', [], SCV_PRODUCTS_VER, true);
    wp_register_script(
        'scv-products-block',
        SCV_PRODUCTS_URL . 'assets/block.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render'],
        SCV_PRODUCTS_VER,
        true
    );
}
add_action('init', 'scv_products_register_assets');

add_action('wp_enqueue_scripts', function () {
    if (!is_singular()) {
        return;
    }
    $post = get_post();
    if (!$post) {
        return;
    }
    if (has_block('scv/products', $post) || has_shortcode($post->post_content, 'scv_products')) {
        wp_enqueue_style('scv-products-front');
        wp_enqueue_script('scv-products-front');
    }
});

add_action('enqueue_block_assets', function () {
    if (is_admin()) {
        wp_enqueue_style('scv-products-front');
    }
});

function scv_products_format_order_by($raw) {
    $raw = trim((string) $raw);
    if ($raw === '') {
        return '';
    }
    $dt = DateTime::createFromFormat('Y-m-d', $raw);
    if ($dt && $dt->format('Y-m-d') === $raw) {
        return 'Order by ' . $dt->format('M j, Y');
    }
    return stripos($raw, 'order by') === 0 ? $raw : 'Order by ' . $raw;
}

function scv_products_photo_url($pid) {
    $pid = (int) $pid;
    if ($pid <= 0) {
        return '';
    }
    $url = wp_get_attachment_image_url($pid, 'full');
    return $url ? $url : '';
}

function scv_products_normalize_status($status) {
    $status = strtolower(trim((string) $status));
    $status = preg_replace('/[^a-z0-9_]/', '', $status);
    if ($status === '' || $status === 'available') {
        return 'live';
    }
    if (!in_array($status, ['live', 'out_of_stock', 'coming_soon'], true)) {
        return 'live';
    }
    return $status;
}

function scv_products_is_live_status($status) {
    return scv_products_normalize_status($status) === 'live';
}


function scv_products_pick_initial_color($colors, $default_key = '') {
    if (!is_array($colors) || $colors === []) {
        return null;
    }
    $default_key = (string) $default_key;
    if ($default_key !== '') {
        foreach ($colors as $c) {
            if ((string) ($c['key'] ?? '') === $default_key && scv_products_is_live_status($c['status'] ?? 'live')) {
                return $c;
            }
        }
    }
    foreach ($colors as $c) {
        if (scv_products_is_live_status($c['status'] ?? 'live')) {
            return $c;
        }
    }
    return $colors[0];
}


function scv_products_color_provider($color, $product_provider) {
    $from_color = trim((string) ($color['provider'] ?? ''));
    if ($from_color !== '') {
        return $from_color;
    }
    $from_product = trim((string) $product_provider);
    return $from_product !== '' ? $from_product : 'Zazzle';
}

function scv_products_shop_label($provider) {
    return 'Shop on ' . $provider;
}

function scv_products_unavailable_label($status) {
    return scv_products_normalize_status($status) === 'coming_soon' ? 'Coming soon' : 'Sold out';
}

function scv_products_unavailable_cta_label($status) {
    return scv_products_normalize_status($status) === 'coming_soon' ? 'COMING SOON' : 'SOLD OUT';
}

function scv_products_unavailable_aria($status) {
    return scv_products_normalize_status($status) === 'coming_soon' ? 'coming soon' : 'sold out';
}

function scv_products_card_status_badge($colors) {
    $has_live = false;
    $all_coming_soon = true;
    foreach ($colors as $c) {
        $st = scv_products_normalize_status($c['status'] ?? 'live');
        if ($st === 'live') {
            $has_live = true;
            $all_coming_soon = false;
            break;
        }
        if ($st !== 'coming_soon') {
            $all_coming_soon = false;
        }
    }
    if ($has_live || empty($colors)) {
        return '';
    }
    return $all_coming_soon ? 'Coming soon' : 'Sold out';
}

function scv_products_render_shop_cta($color, $product_provider) {
    $status = scv_products_normalize_status($color['status'] ?? 'live');
    $is_live = ($status === 'live');
    $provider = scv_products_color_provider($color, $product_provider);
    $url = (string) ($color['url'] ?? '');
    $slot_class = 'wp-block-button sv-cta-slot' . ($is_live ? ' is-live' : ' is-unavailable');
    ?>
    <div class="<?php echo esc_attr($slot_class); ?>" data-swatch-cta>
        <a class="wp-block-button__link wp-element-button" data-swatch-btn data-swatch-shop<?php echo ($is_live && $url !== '') ? ' href="' . esc_url($url) . '"' : ''; ?> target="_blank" rel="noreferrer noopener"<?php echo $is_live ? '' : ' tabindex="-1" aria-hidden="true"'; ?>><?php echo esc_html(scv_products_shop_label($provider)); ?></a>
        <span class="wp-block-button__link wp-element-button sv-shop-status" data-swatch-status aria-disabled="true"<?php echo $is_live ? ' aria-hidden="true"' : ''; ?>><?php echo esc_html($is_live ? 'SOLD OUT' : scv_products_unavailable_cta_label($status)); ?></span>
    </div>
    <?php
}

function scv_products_render_card($post, $show_count_pill = true) {
    $pid = $post->ID;
    $title = get_the_title($post);
    $garment = (string) get_post_meta($pid, '_scv_garment', true);
    $provider = (string) get_post_meta($pid, '_scv_provider', true);
    if ($provider === '') {
        $provider = 'Zazzle';
    }
    $short_desc = (string) get_post_meta($pid, '_scv_short_desc', true);
    $hide_swatches = (bool) get_post_meta($pid, '_scv_hide_swatches', true);
    $aria_label = (string) get_post_meta($pid, '_scv_swatch_aria_label', true);
    if ($aria_label === '') {
        $aria_label = $title . ' color';
    }

    $raw_colors = get_post_meta($pid, '_scv_colors', true);
    if (!is_array($raw_colors)) {
        $raw_colors = [];
    }

    $colors = [];
    foreach ($raw_colors as $c) {
        $photos = array_values(array_filter(array_map('intval', (array) ($c['photos'] ?? []))));
        $imgs = [];
        foreach ($photos as $photo_id) {
            $u = scv_products_photo_url($photo_id);
            if ($u !== '') {
                $imgs[] = $u;
            }
        }
        $primary_id = !empty($photos) ? $photos[0] : 0;
        $primary_img = !empty($imgs) ? $imgs[0] : '';
        $order_by = (string) ($c['order_by'] ?? '');
        $status = scv_products_normalize_status($c['status'] ?? 'live');
        $colors[] = [
            'key'         => (string) ($c['key'] ?? 'default'),
            'label'       => (string) ($c['label'] ?? ''),
            'status'      => $status,
            'swatch'      => (string) ($c['swatch'] ?? ''),
            'photoId'     => $primary_id,
            'img'         => $primary_img,
            'imgs'        => $imgs,
            'url'         => (string) ($c['url'] ?? ''),
            'orderBy'     => $order_by,
            'orderByText' => scv_products_format_order_by($order_by),
            'provider'    => trim((string) ($c['provider'] ?? '')),
        ];
    }

    if (empty($colors)) {
        return '';
    }

    $default_key = (string) get_post_meta($pid, '_scv_default_color', true);
    $first = scv_products_pick_initial_color($colors, $default_key);
    if (!$first) {
        return '';
    }

    $has_any_live = false;
    foreach ($colors as $c) {
        if (scv_products_is_live_status($c['status'])) {
            $has_any_live = true;
            break;
        }
    }

    $has_swatch_ui = !$hide_swatches && count($colors) >= 2;
    $badge = scv_products_card_status_badge($colors);
    $card_classes = 'wp-block-group sv-card';
    if ($has_swatch_ui) {
        $card_classes .= ' sv-swatch-card';
    }
    if ($badge !== '') {
        $card_classes .= ' sv-card-unavailable';
    }

    ob_start();
    if ($has_swatch_ui) {
        $has_any_order_by = false;
        foreach ($colors as $c) {
            if ($c['orderByText'] !== '') {
                $has_any_order_by = true;
                break;
            }
        }
        $alt = $first['label'] !== '' ? ($title . ' in ' . $first['label']) : $title;
        $payload = [
            'id'           => $post->post_name,
            'title'        => $title,
            'provider'     => $provider,
            'defaultColor' => $default_key,
            'hero'         => $first['key'],
            'colors'       => $colors,
        ];
        ?>
        <div class="<?php echo esc_attr($card_classes); ?>" data-swatch-card>
            <figure class="wp-block-image size-full sv-swatch-figure">
                <?php if ($first['img'] !== '') : ?>
                    <a class="sv-card-zoom" href="<?php echo esc_url($first['img']); ?>" data-swatch-zoom aria-label="<?php echo esc_attr('View larger photo of ' . $alt); ?>">
                        <img data-swatch-img src="<?php echo esc_url($first['img']); ?>" alt="<?php echo esc_attr($alt); ?>" class="<?php echo $first['photoId'] ? esc_attr('wp-image-' . $first['photoId']) : ''; ?>" loading="lazy" decoding="async">
                    </a>
                <?php endif; ?>
                <?php if ($badge !== '') : ?>
                    <span class="sv-status-badge"><?php echo esc_html($badge); ?></span>
                <?php elseif ($show_count_pill && count($colors) > 1) : ?>
                    <span class="sv-swatch-pill"><?php echo (int) count($colors); ?> colors</span>
                <?php endif; ?>
            </figure>
            <div class="wp-block-group sv-card-body">
                <?php if ($garment !== '') : ?>
                    <p class="sv-meta"><?php echo esc_html($garment); ?></p>
                <?php endif; ?>
                <h3 class="wp-block-heading"><?php echo esc_html($title); ?></h3>
                <?php if ($short_desc !== '') : ?>
                    <p><?php echo esc_html($short_desc); ?></p>
                <?php endif; ?>
                <div class="sv-swatch-pick">
                    <span class="sv-swatch-lab">Color: <b data-swatch-label><?php echo esc_html($first['label']); ?></b></span>
                    <div class="sv-swatch-dots" role="group" aria-label="<?php echo esc_attr($aria_label); ?>">
                        <?php foreach ($colors as $c) :
                            $is_on = ($c['key'] === $first['key']);
                            $hex = $c['swatch'] !== '' ? $c['swatch'] : '#111111';
                            $live = scv_products_is_live_status($c['status']);
                            $dot_class = 'sv-swatch-dot' . ($is_on ? ' is-on' : '');
                            $state = $live ? 'available' : $c['status'];
                            $dot_aria = $c['label'];
                            if (!$live) {
                                $dot_class .= ' is-unavailable';
                                if ($c['status'] === 'coming_soon') {
                                    $dot_class .= ' is-coming-soon';
                                } else {
                                    $dot_class .= ' is-sold-out';
                                }
                                $dot_aria = $c['label'] !== ''
                                    ? ($c['label'] . ', ' . scv_products_unavailable_aria($c['status']))
                                    : scv_products_unavailable_label($c['status']);
                            }
                            ?>
                            <button type="button" class="<?php echo esc_attr($dot_class); ?>" data-color-key="<?php echo esc_attr($c['key']); ?>" data-state="<?php echo esc_attr($state); ?>" aria-label="<?php echo esc_attr($dot_aria); ?>" aria-pressed="<?php echo $is_on ? 'true' : 'false'; ?>"<?php echo $live ? '' : ' aria-disabled="true"'; ?> style="background:<?php echo esc_attr($hex); ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($has_any_order_by) : ?>
                    <p class="sv-order-by sv-small" data-swatch-orderby<?php echo $first['orderByText'] === '' ? ' hidden' : ''; ?>><?php echo esc_html($first['orderByText']); ?></p>
                <?php endif; ?>
                <div class="wp-block-buttons">
                    <?php scv_products_render_shop_cta($first, $provider); ?>
                </div>
            </div>
            <script type="application/json" class="sv-swatch-data"><?php echo wp_json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES); ?></script>
        </div>
        <?php
    } else {
        $alt = $first['label'] !== '' ? ($title . ' in ' . $first['label']) : $title;
        ?>
        <div class="<?php echo esc_attr($card_classes); ?>">
            <figure class="wp-block-image size-full">
                <?php if ($first['img'] !== '') : ?>
                    <a class="sv-card-zoom" href="<?php echo esc_url($first['img']); ?>" aria-label="<?php echo esc_attr('View larger photo of ' . $alt); ?>">
                        <img src="<?php echo esc_url($first['img']); ?>" alt="<?php echo esc_attr($alt); ?>" class="<?php echo $first['photoId'] ? esc_attr('wp-image-' . $first['photoId']) : ''; ?>" loading="lazy" decoding="async">
                    </a>
                <?php endif; ?>
                <?php if ($badge !== '') : ?>
                    <span class="sv-status-badge"><?php echo esc_html($badge); ?></span>
                <?php endif; ?>
            </figure>
            <div class="wp-block-group sv-card-body">
                <?php if ($garment !== '') : ?>
                    <p class="sv-meta"><?php echo esc_html($garment); ?></p>
                <?php endif; ?>
                <h3 class="wp-block-heading"><?php echo esc_html($title); ?></h3>
                <?php if ($short_desc !== '') : ?>
                    <p><?php echo esc_html($short_desc); ?></p>
                <?php endif; ?>
                <?php if ($first['orderByText'] !== '') : ?>
                    <p class="sv-order-by sv-small"><?php echo esc_html($first['orderByText']); ?></p>
                <?php endif; ?>
                <div class="wp-block-buttons">
                    <?php scv_products_render_shop_cta($first, $provider); ?>
                </div>
            </div>
        </div>
        <?php
    }
    return ob_get_clean();
}

function scv_products_collection_pill_label($name) {
    $label = trim((string) preg_replace('/\s+collection$/i', '', (string) $name));
    return $label !== '' ? $label : (string) $name;
}

function scv_products_is_more_gear_term($term) {
    $slug = strtolower((string) ($term->slug ?? ''));
    $name = strtolower(trim((string) ($term->name ?? '')));
    return $slug === 'more-gear' || $name === 'more gear';
}

function scv_products_parse_bool_attr($val, $default = false) {
    if (is_bool($val)) {
        return $val;
    }
    if (is_int($val) || is_float($val)) {
        return ((int) $val) !== 0;
    }
    if (is_string($val)) {
        $v = strtolower(trim($val));
        if ($v === '' || $v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
            return false;
        }
        if ($v === '1' || $v === 'true' || $v === 'yes' || $v === 'on') {
            return true;
        }
    }
    return $default;
}


function scv_products_category_matches_master($term, $master_heading) {
    $heading = trim(html_entity_decode((string) $master_heading, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $name = trim(html_entity_decode((string) ($term->name ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($heading !== '' && strcasecmp($name, $heading) === 0) {
        return true;
    }
    $heading_slug = sanitize_title($heading);
    $term_slug = (string) ($term->slug ?? '');
    return $heading_slug !== '' && $term_slug !== '' && $term_slug === $heading_slug;
}

function scv_products_render($attrs = []) {
    wp_enqueue_style('scv-products-front');
    wp_enqueue_script('scv-products-front');

    $collection_filter = sanitize_title($attrs['collection'] ?? '');
    $other_heading = array_key_exists('otherHeading', $attrs)
        ? sanitize_text_field((string) $attrs['otherHeading'])
        : (array_key_exists('other_heading', $attrs) ? sanitize_text_field((string) $attrs['other_heading']) : 'More Gear');
    $show_count_pill = array_key_exists('showCountPill', $attrs) ? (bool) $attrs['showCountPill'] : true;
    $collection_nav_raw = array_key_exists('collectionNav', $attrs)
        ? $attrs['collectionNav']
        : (array_key_exists('collection_nav', $attrs) ? $attrs['collection_nav'] : false);
    $collection_nav = ($collection_filter === '') && scv_products_parse_bool_attr($collection_nav_raw, false);
    $master_heading = array_key_exists('masterHeading', $attrs)
        ? sanitize_text_field((string) $attrs['masterHeading'])
        : (array_key_exists('master_heading', $attrs) ? sanitize_text_field((string) $attrs['master_heading']) : 'Sweatshirts & Hoodies');
    if ($master_heading === '') {
        $master_heading = 'Sweatshirts & Hoodies';
    }
    $nav_label = array_key_exists('navLabel', $attrs)
        ? sanitize_text_field((string) $attrs['navLabel'])
        : (array_key_exists('nav_label', $attrs) ? sanitize_text_field((string) $attrs['nav_label']) : 'Choose collection:');
    if ($nav_label === '') {
        $nav_label = 'Choose collection:';
    }

    $query_args = [
        'post_type'      => 'scv_product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
    ];
    if ($collection_filter !== '') {
        $query_args['tax_query'] = [[
            'taxonomy' => 'scv_collection',
            'field'    => 'slug',
            'terms'    => $collection_filter,
        ]];
    }

    $posts = get_posts($query_args);
    if (empty($posts)) {
        return is_admin() ? '<p class="sv-small">No live products found yet.</p>' : '';
    }

    usort($posts, function ($a, $b) {
        $oa = get_post_meta($a->ID, '_scv_sort_order', true);
        $ob = get_post_meta($b->ID, '_scv_sort_order', true);
        $ia = ($oa === '') ? 10 : (int) $oa;
        $ib = ($ob === '') ? 10 : (int) $ob;
        if ($ia !== $ib) {
            return $ia <=> $ib;
        }
        return strcasecmp($a->post_title, $b->post_title);
    });

    $terms = get_terms([
        'taxonomy'   => 'scv_collection',
        'hide_empty' => true,
    ]);
    if (is_wp_error($terms)) {
        $terms = [];
    }
    usort($terms, function ($a, $b) {
        $oa = get_term_meta($a->term_id, '_scv_collection_order', true);
        $ob = get_term_meta($b->term_id, '_scv_collection_order', true);
        $ia = ($oa === '') ? 100 : (int) $oa;
        $ib = ($ob === '') ? 100 : (int) $ob;
        if ($ia !== $ib) {
            return $ia <=> $ib;
        }
        return strcasecmp($a->name, $b->name);
    });

    $groups = [];
    foreach ($terms as $t) {
        if ($collection_filter !== '' && $t->slug !== $collection_filter) {
            continue;
        }
        $pill_label = scv_products_collection_pill_label($t->name);
        $groups[$t->term_id] = [
            'term_id'      => (int) $t->term_id,
            'slug'         => (string) $t->slug,
            'short_slug'   => sanitize_title($pill_label),
            'pill_label'   => $pill_label,
            'heading'      => (string) $t->name,
            'is_more_gear' => scv_products_is_more_gear_term($t),
            'cards'        => [],
        ];
    }

    if (!$collection_nav) {
        $uncollected_cards = [];
        foreach ($posts as $p) {
            $card_html = scv_products_render_card($p, $show_count_pill);
            if ($card_html === '') {
                continue;
            }
            $p_terms = get_the_terms($p->ID, 'scv_collection');
            if (is_array($p_terms) && !empty($p_terms)) {
                foreach ($p_terms as $pt) {
                    if (isset($groups[$pt->term_id])) {
                        $groups[$pt->term_id]['cards'][] = $card_html;
                    }
                }
            } elseif ($collection_filter === '') {
                $uncollected_cards[] = $card_html;
            }
        }

        $rendered_groups = [];
        foreach ($groups as $g) {
            if (!empty($g['cards'])) {
                $rendered_groups[] = $g;
            }
        }
        if (!empty($uncollected_cards)) {
            $rendered_groups[] = [
                'heading' => $other_heading,
                'cards'   => $uncollected_cards,
            ];
        }

        if (empty($rendered_groups)) {
            return is_admin() ? '<p class="sv-small">No live products match this view.</p>' : '';
        }

        ob_start();
        echo '<div class="scv-products-block">';
        foreach ($rendered_groups as $g) {
            echo '<div class="sv-collection-group sv-inner">';
            if ($g['heading'] !== '') {
                echo '<h2 class="wp-block-heading sv-collection-heading">' . esc_html($g['heading']) . '</h2>';
            }
            echo '<div class="wp-block-group sv-products">';
            echo implode("\n", $g['cards']);
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    $category_terms = get_terms([
        'taxonomy'   => 'scv_category',
        'hide_empty' => true,
    ]);
    if (is_wp_error($category_terms)) {
        $category_terms = [];
    }
    usort($category_terms, function ($a, $b) {
        $oa = get_term_meta($a->term_id, '_scv_category_order', true);
        $ob = get_term_meta($b->term_id, '_scv_category_order', true);
        $ia = ($oa === '') ? 100 : (int) $oa;
        $ib = ($ob === '') ? 100 : (int) $ob;
        if ($ia !== $ib) {
            return $ia <=> $ib;
        }
        return strcasecmp($a->name, $b->name);
    });

    $category_sections = [];
    $master_cat_id = 0;
    foreach ($category_terms as $ct) {
        $cat_groups = [];
        foreach ($groups as $cid => $g) {
            if (!empty($g['is_more_gear'])) {
                continue;
            }
            $cat_groups[$cid] = $g;
        }
        $category_sections[(int) $ct->term_id] = [
            'term_id' => (int) $ct->term_id,
            'slug'    => (string) $ct->slug,
            'heading' => (string) $ct->name,
            'groups'  => $cat_groups,
        ];
        if ($master_cat_id === 0 && scv_products_category_matches_master($ct, $master_heading)) {
            $master_cat_id = (int) $ct->term_id;
        }
    }

    $fallback_groups = [];
    foreach ($groups as $cid => $g) {
        if (!empty($g['is_more_gear'])) {
            continue;
        }
        $fallback_groups[$cid] = $g;
    }

    $more_gear_cards = [];
    foreach ($posts as $p) {
        $card_html = scv_products_render_card($p, $show_count_pill);
        if ($card_html === '') {
            continue;
        }
        $p_colls = get_the_terms($p->ID, 'scv_collection');
        if (is_wp_error($p_colls) || !is_array($p_colls) || empty($p_colls)) {
            $more_gear_cards[] = $card_html;
            continue;
        }

        $non_more_colls = [];
        $in_more_gear = false;
        foreach ($p_colls as $pt) {
            if (scv_products_is_more_gear_term($pt)) {
                $in_more_gear = true;
            } elseif (isset($groups[$pt->term_id])) {
                $non_more_colls[] = $pt;
            }
        }
        if ($in_more_gear) {
            $more_gear_cards[] = $card_html;
        }
        if (empty($non_more_colls)) {
            continue;
        }

        $p_cats = get_the_terms($p->ID, 'scv_category');
        $valid_cats = [];
        if (is_array($p_cats) && !empty($p_cats)) {
            foreach ($p_cats as $pc) {
                if (isset($category_sections[(int) $pc->term_id])) {
                    $valid_cats[] = (int) $pc->term_id;
                }
            }
        }

        if (!empty($valid_cats)) {
            foreach ($valid_cats as $cat_id) {
                foreach ($non_more_colls as $pt) {
                    if (isset($category_sections[$cat_id]['groups'][$pt->term_id])) {
                        $category_sections[$cat_id]['groups'][$pt->term_id]['cards'][] = $card_html;
                    }
                }
            }
        } elseif ($master_cat_id > 0 && isset($category_sections[$master_cat_id])) {
            foreach ($non_more_colls as $pt) {
                if (isset($category_sections[$master_cat_id]['groups'][$pt->term_id])) {
                    $category_sections[$master_cat_id]['groups'][$pt->term_id]['cards'][] = $card_html;
                }
            }
        } else {
            foreach ($non_more_colls as $pt) {
                if (isset($fallback_groups[$pt->term_id])) {
                    $fallback_groups[$pt->term_id]['cards'][] = $card_html;
                }
            }
        }
    }

    $rendered_sections = [];
    $active_fallback = [];
    foreach ($fallback_groups as $g) {
        if (!empty($g['cards'])) {
            $active_fallback[] = $g;
        }
    }
    if (!empty($active_fallback)) {
        $rendered_sections[] = [
            'slug'    => sanitize_title($master_heading),
            'heading' => $master_heading,
            'groups'  => $active_fallback,
        ];
    }
    foreach ($category_sections as $sec) {
        $active_groups = [];
        foreach ($sec['groups'] as $g) {
            if (!empty($g['cards'])) {
                $active_groups[] = $g;
            }
        }
        if (!empty($active_groups)) {
            $rendered_sections[] = [
                'slug'    => $sec['slug'],
                'heading' => $sec['heading'],
                'groups'  => $active_groups,
            ];
        }
    }

    if (empty($rendered_sections) && empty($more_gear_cards)) {
        return is_admin() ? '<p class="sv-small">No live products match this view.</p>' : '';
    }

    static $scv_nav_instance = 0;
    ob_start();
    echo '<div class="scv-products-block scv-products-block--nav">';
    foreach ($rendered_sections as $sec) {
        $scv_nav_instance++;
        $label_id = function_exists('wp_unique_id')
            ? wp_unique_id('scv-filter-label-')
            : ('scv-filter-label-' . $scv_nav_instance);
        $section_id = function_exists('wp_unique_id')
            ? wp_unique_id('scv-cat-section-')
            : ('scv-cat-section-' . $scv_nav_instance);
        $sec_groups = $sec['groups'];
        echo '<div class="sv-master-section sv-inner" id="' . esc_attr($section_id) . '" data-scv-collection-section data-scv-category="' . esc_attr($sec['slug']) . '">';
        if ($sec['heading'] !== '') {
            echo '<h2 class="wp-block-heading sv-collection-heading sv-master-heading">' . esc_html($sec['heading']) . '</h2>';
        }
        echo '<div class="sv-filter-wrap" data-scv-filter-wrap>';
        echo '<p class="sv-filter-label" id="' . esc_attr($label_id) . '">' . esc_html($nav_label) . '</p>';
        echo '<div class="sv-filter-viewport" data-scv-filter-viewport>';
        echo '<div class="sv-filter-bar" role="radiogroup" aria-labelledby="' . esc_attr($label_id) . '" data-scv-filter-bar>';
        foreach ($sec_groups as $idx => $g) {
            $is_default = ($idx === 0);
            echo '<button type="button" class="sv-filter-pill' . ($is_default ? ' is-active' : '') . '" role="radio"'
                . ' aria-checked="' . ($is_default ? 'true' : 'false') . '"'
                . ' tabindex="' . ($is_default ? '0' : '-1') . '"'
                . ' data-collection-pill="' . esc_attr($g['slug']) . '"'
                . ' data-collection-short="' . esc_attr($g['short_slug']) . '"'
                . ' data-collection-label="' . esc_attr($g['heading']) . '"'
                . '>' . esc_html($g['pill_label']) . '</button>';
        }
        echo '<button type="button" class="sv-filter-pill" role="radio" aria-checked="false" tabindex="-1" data-collection-pill="all" data-collection-label="All">All</button>';
        echo '</div>';
        echo '</div>';
        echo '<span class="sv-sr-only" aria-live="polite" data-scv-filter-status></span>';
        echo '</div>';
        echo '<div class="sv-collections-list" data-scv-collections-list>';
        foreach ($sec_groups as $idx => $g) {
            $is_default = ($idx === 0);
            echo '<div class="sv-collection-group"'
                . ' id="' . esc_attr($section_id . '-' . $g['slug']) . '"'
                . ' data-collection-group="' . esc_attr($g['slug']) . '"'
                . ' data-collection-short="' . esc_attr($g['short_slug']) . '"'
                . ' data-collection-count="' . (int) count($g['cards']) . '"'
                . ($is_default ? '' : ' hidden')
                . '>';
            if ($g['heading'] !== '') {
                echo '<h3 class="wp-block-heading sv-collection-subheading">' . esc_html($g['heading']) . '</h3>';
            }
            echo '<div class="wp-block-group sv-products">';
            echo implode("\n", $g['cards']);
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }
    if (!empty($more_gear_cards)) {
        echo '<div class="sv-collection-group sv-inner sv-more-gear-group">';
        if ($other_heading !== '') {
            echo '<h2 class="wp-block-heading sv-collection-heading">' . esc_html($other_heading) . '</h2>';
        }
        echo '<div class="wp-block-group sv-products">';
        echo implode("\n", $more_gear_cards);
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    return ob_get_clean();
}

add_action('init', function () {
    register_block_type('scv/products', [
        'api_version'     => 2,
        'editor_script'   => 'scv-products-block',
        'style'           => 'scv-products-front',
        'script'          => 'scv-products-front',
        'render_callback' => 'scv_products_render',
        'attributes'      => [
            'collection'    => ['type' => 'string', 'default' => ''],
            'otherHeading'  => ['type' => 'string', 'default' => 'More Gear'],
            'showCountPill' => ['type' => 'boolean', 'default' => true],
            'collectionNav' => ['type' => 'boolean', 'default' => false],
            'masterHeading' => ['type' => 'string', 'default' => 'Sweatshirts & Hoodies'],
            'navLabel'      => ['type' => 'string', 'default' => 'Choose collection:'],
        ],
    ]);

    add_shortcode('scv_products', function ($atts) {
        $atts = shortcode_atts([
            'collection'     => '',
            'other_heading'  => 'More Gear',
            'show_pill'      => '1',
            'collection_nav' => '0',
            'master_heading' => 'Sweatshirts & Hoodies',
            'nav_label'      => 'Choose collection:',
        ], $atts, 'scv_products');
        return scv_products_render([
            'collection'    => $atts['collection'],
            'otherHeading'  => $atts['other_heading'],
            'showCountPill' => ($atts['show_pill'] !== '0' && $atts['show_pill'] !== 'false'),
            'collectionNav' => scv_products_parse_bool_attr($atts['collection_nav'], false),
            'masterHeading' => $atts['master_heading'],
            'navLabel'      => $atts['nav_label'],
        ]);
    });
});

/* --------------------------------------------------------------------------
 * One-time activation seed of the 7 current Always Available items
 * -------------------------------------------------------------------------- */

function scv_products_seed_initial_catalog() {
    try {
    scv_products_register_types();

    if (get_option('scv_products_seeded')) {
        delete_option('scv_products_needs_seed');
        return;
    }
    $existing = get_posts([
        'post_type'      => 'scv_product',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ]);
    if (!empty($existing)) {
        update_option('scv_products_seeded', '1', false);
        delete_option('scv_products_needs_seed');
        return;
    }

    $classic_term_id = 0;
    $existing_term = term_exists('Classic Collection', 'scv_collection');
    if (is_array($existing_term)) {
        $classic_term_id = (int) $existing_term['term_id'];
    } elseif (is_numeric($existing_term)) {
        $classic_term_id = (int) $existing_term;
    } else {
        $ins = wp_insert_term('Classic Collection', 'scv_collection', [
            'slug'        => 'classic-collection',
            'description' => 'Est. 1996 full-color embroidered garments.',
        ]);
        if (!is_wp_error($ins) && !empty($ins['term_id'])) {
            $classic_term_id = (int) $ins['term_id'];
        }
    }
    if ($classic_term_id > 0) {
        update_term_meta($classic_term_id, '_scv_collection_order', 10);
    }

    $seed = [
        [
            'title'         => 'SoCal VoCals Est. 1996 Embroidered Pullover Hoodie',
            'slug'          => 'est1996-pullover-hoodie',
            'collection'    => $classic_term_id,
            'garment'       => 'Hoodies & Sweatshirts',
            'provider'      => 'Zazzle',
            'short_desc'    => 'Pullover hoodie with the logo stitched on the chest.',
            'sort_order'    => 10,
            'hide_swatches' => false,
            'aria_label'    => 'Hoodie color',
            'colors'        => [
                [
                    'key'      => 'black',
                    'label'    => 'Black',
                    'swatch'   => '#111111',
                    'url'      => 'https://www.zazzle.com/256430471596163977?color=black',
                    'photos'   => [197],
                    'status'   => 'live',
                    'order_by' => '',
                ],
                [
                    'key'      => 'white',
                    'label'    => 'White',
                    'swatch'   => '#f6f6f4',
                    'url'      => 'https://www.zazzle.com/256430471596163977?color=white',
                    'photos'   => [198],
                    'status'   => 'live',
                    'order_by' => '',
                ],
            ],
        ],
        [
            'title'         => 'SoCal VoCals Est. 1996 Women’s Pullover Hoodie',
            'slug'          => 'est1996-womens-pullover-hoodie',
            'collection'    => $classic_term_id,
            'garment'       => 'Hoodies & Sweatshirts',
            'provider'      => 'Zazzle',
            'short_desc'    => 'Women’s cut, full-color logo on the front.',
            'sort_order'    => 20,
            'hide_swatches' => false,
            'aria_label'    => "Women's hoodie color",
            'colors'        => [
                [
                    'key'      => 'black',
                    'label'    => 'Black',
                    'swatch'   => '#111111',
                    'url'      => 'https://www.zazzle.com/256178150599914112?color=black',
                    'photos'   => [199],
                    'status'   => 'live',
                    'order_by' => '',
                ],
                [
                    'key'      => 'ash',
                    'label'    => 'Ash',
                    'swatch'   => '#d0cdc8',
                    'url'      => 'https://www.zazzle.com/256178150599914112?color=ash',
                    'photos'   => [200],
                    'status'   => 'live',
                    'order_by' => '',
                ],
                [
                    'key'      => 'white',
                    'label'    => 'White',
                    'swatch'   => '#f6f6f4',
                    'url'      => 'https://www.zazzle.com/256178150599914112?color=white',
                    'photos'   => [201],
                    'status'   => 'live',
                    'order_by' => '',
                ],
            ],
        ],
        [
            'title'         => 'SoCal VoCals Est. 1996 Embroidered Sweatshirt',
            'slug'          => 'est1996-sweatshirt',
            'collection'    => $classic_term_id,
            'garment'       => 'Hoodies & Sweatshirts',
            'provider'      => 'Zazzle',
            'short_desc'    => 'Classic crewneck with the full-color logo.',
            'sort_order'    => 30,
            'hide_swatches' => false,
            'aria_label'    => 'Sweatshirt color',
            'colors'        => [
                [
                    'key'      => 'black',
                    'label'    => 'Black',
                    'swatch'   => '#111111',
                    'url'      => 'https://www.zazzle.com/socal_vocals_est_1996_embroidered_sweatshirt-256705831595179237?color=black',
                    'photos'   => [190],
                    'status'   => 'live',
                    'order_by' => '',
                ],
                [
                    'key'      => 'ash',
                    'label'    => 'Ash',
                    'swatch'   => '#d0cdc8',
                    'url'      => 'https://www.zazzle.com/socal_vocals_est_1996_embroidered_sweatshirt-256705831595179237?color=ash',
                    'photos'   => [191],
                    'status'   => 'live',
                    'order_by' => '',
                ],
                [
                    'key'      => 'white',
                    'label'    => 'White',
                    'swatch'   => '#f6f6f4',
                    'url'      => 'https://www.zazzle.com/socal_vocals_est_1996_embroidered_sweatshirt-256705831595179237?color=white',
                    'photos'   => [192],
                    'status'   => 'live',
                    'order_by' => '',
                ],
            ],
        ],
        [
            'title'         => "SoCal VoCals Est. 1996 Women's Sweatshirt",
            'slug'          => 'est1996-womens-sweatshirt',
            'collection'    => $classic_term_id,
            'garment'       => 'Hoodies & Sweatshirts',
            'provider'      => 'Zazzle',
            'short_desc'    => "Women's cut, classic crewneck with the full-color logo.",
            'sort_order'    => 40,
            'hide_swatches' => false,
            'aria_label'    => "Women's sweatshirt color",
            'colors'        => [
                [
                    'key'      => 'black',
                    'label'    => 'Black',
                    'swatch'   => '#111111',
                    'url'      => 'https://www.zazzle.com/256726247272383672?color=black',
                    'photos'   => [193],
                    'status'   => 'live',
                    'order_by' => '',
                ],
                [
                    'key'      => 'ash',
                    'label'    => 'Ash',
                    'swatch'   => '#d0cdc8',
                    'url'      => 'https://www.zazzle.com/256726247272383672?color=ash',
                    'photos'   => [194],
                    'status'   => 'live',
                    'order_by' => '',
                ],
                [
                    'key'      => 'white',
                    'label'    => 'White',
                    'swatch'   => '#f6f6f4',
                    'url'      => 'https://www.zazzle.com/256726247272383672?color=white',
                    'photos'   => [195],
                    'status'   => 'live',
                    'order_by' => '',
                ],
            ],
        ],
        [
            'title'         => 'SoCal VoCals Embroidered Zip-UP Hoodie',
            'slug'          => 'embroidered-zip-up-hoodie',
            'collection'    => 0,
            'garment'       => 'Hoodies & Sweatshirts',
            'provider'      => 'Zazzle',
            'short_desc'    => 'Full-zip hoodie with a stitched left-chest logo.',
            'sort_order'    => 50,
            'hide_swatches' => true,
            'aria_label'    => '',
            'colors'        => [
                [
                    'key'      => 'default',
                    'label'    => '',
                    'swatch'   => '',
                    'url'      => 'https://www.zazzle.com/socal_vocals_logo_embroidered_zip_hoodie-256799880297464531',
                    'photos'   => [19],
                    'status'   => 'live',
                    'order_by' => '',
                ],
            ],
        ],
        [
            'title'         => 'SoCal VoCals Logo Hat',
            'slug'          => 'logo-hat',
            'collection'    => 0,
            'garment'       => 'Hats',
            'provider'      => 'Zazzle',
            'short_desc'    => 'Embroidered logo on a structured cap.',
            'sort_order'    => 60,
            'hide_swatches' => true,
            'aria_label'    => '',
            'colors'        => [
                [
                    'key'      => 'default',
                    'label'    => '',
                    'swatch'   => '',
                    'url'      => 'https://www.zazzle.com/socal_vocals_logo_hat-256151186900112745',
                    'photos'   => [17],
                    'status'   => 'live',
                    'order_by' => '',
                ],
            ],
        ],
        [
            'title'         => 'SoCal VoCals Logo Phone Case',
            'slug'          => 'logo-phone-case',
            'collection'    => 0,
            'garment'       => 'Accessories',
            'provider'      => 'Zazzle',
            'short_desc'    => 'The logo, big, wrapped around your phone.',
            'sort_order'    => 70,
            'hide_swatches' => true,
            'aria_label'    => '',
            'colors'        => [
                [
                    'key'      => 'default',
                    'label'    => '',
                    'swatch'   => '',
                    'url'      => 'https://www.zazzle.com/socal_vocals_logo_phone_case-256104817506011960',
                    'photos'   => [22],
                    'status'   => 'live',
                    'order_by' => '',
                ],
            ],
        ],
    ];

    foreach ($seed as $item) {
        $post_id = wp_insert_post([
            'post_type'   => 'scv_product',
            'post_status' => 'publish',
            'post_title'  => $item['title'],
            'post_name'   => $item['slug'],
        ]);
        if (is_wp_error($post_id) || !$post_id) {
            continue;
        }
        update_post_meta($post_id, '_scv_garment', $item['garment']);
        update_post_meta($post_id, '_scv_provider', $item['provider']);
        update_post_meta($post_id, '_scv_short_desc', $item['short_desc']);
        update_post_meta($post_id, '_scv_sort_order', (int) $item['sort_order']);
        update_post_meta($post_id, '_scv_hide_swatches', (bool) $item['hide_swatches']);
        update_post_meta($post_id, '_scv_swatch_aria_label', $item['aria_label']);
        update_post_meta($post_id, '_scv_colors', scv_products_sanitize_colors($item['colors']));
        if (!empty($item['collection'])) {
            wp_set_object_terms($post_id, [(int) $item['collection']], 'scv_collection');
        }
    }

    update_option('scv_products_seeded', '1', false);
    delete_option('scv_products_needs_seed');
    delete_option('scv_products_seed_error');
    } catch (Throwable $e) {
        error_log('SCV Products seed failed: ' . $e->getMessage());
        update_option('scv_products_seed_error', $e->getMessage(), false);
        delete_option('scv_products_needs_seed');
    }
}

function scv_products_activate() {
    try {
        scv_products_register_types();
        if (!get_option('scv_products_seeded')) {
            update_option('scv_products_needs_seed', '1', false);
        }
    } catch (Throwable $e) {
        error_log('SCV Products activation: ' . $e->getMessage());
    }
}
register_activation_hook(__FILE__, 'scv_products_activate');

function scv_products_maybe_seed() {
    if (get_option('scv_products_seeded') || !get_option('scv_products_needs_seed')) {
        return;
    }
    if (!current_user_can('activate_plugins') && !current_user_can('edit_posts')) {
        return;
    }
    try {
        scv_products_seed_initial_catalog();
    } catch (Throwable $e) {
        error_log('SCV Products seed failed: ' . $e->getMessage());
        update_option('scv_products_seed_error', $e->getMessage(), false);
        delete_option('scv_products_needs_seed');
    }
}
add_action('admin_init', 'scv_products_maybe_seed');
