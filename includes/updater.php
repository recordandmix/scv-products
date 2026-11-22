<?php
/**
 * Self-hosted update checker for SCV Products.
 *
 * Reads a small JSON manifest from the latest GitHub release and surfaces
 * updates in Dashboard > Updates, including the View details popup and the
 * auto-updates toggle. Failures are silent.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('SCV_PRODUCTS_UPDATE_MANIFEST_URL')) {
    return;
}

define('SCV_PRODUCTS_UPDATE_CACHE_KEY', 'scv_products_update_manifest');
define('SCV_PRODUCTS_UPDATE_CACHE_TTL', 600); // 10 minutes

add_action('load-update-core.php', 'scv_products_maybe_flush_update_cache');
add_filter('pre_set_site_transient_update_plugins', 'scv_products_filter_update_plugins');
add_filter('plugins_api', 'scv_products_plugins_api', 10, 3);
add_filter('upgrader_source_selection', 'scv_products_upgrader_source_selection', 10, 4);

/**
 * @return string Plugin basename, e.g. scv-products/scv-products.php
 */
function scv_products_plugin_basename() {
    return plugin_basename(SCV_PRODUCTS_PATH . 'scv-products.php');
}

/**
 * Clear the cached manifest when Matt clicks "Check again" on the Updates screen.
 */
function scv_products_maybe_flush_update_cache() {
    if (!empty($_GET['force-check'])) {
        delete_transient(SCV_PRODUCTS_UPDATE_CACHE_KEY);
    }
}

/**
 * @param bool $force Bypass the short transient cache.
 * @return array|false
 */
function scv_products_get_update_manifest($force = false) {
    if (!empty($_GET['force-check'])) {
        $force = true;
    }

    if (!$force) {
        $cached = get_transient(SCV_PRODUCTS_UPDATE_CACHE_KEY);
        if (is_array($cached) && !empty($cached['version']) && !empty($cached['download_url'])) {
            return $cached;
        }
    } else {
        delete_transient(SCV_PRODUCTS_UPDATE_CACHE_KEY);
    }

    $response = wp_remote_get(SCV_PRODUCTS_UPDATE_MANIFEST_URL, [
        'timeout'     => 10,
        'redirection' => 5,
        'headers'     => [
            'Accept' => 'application/json',
        ],
        'user-agent'  => 'SCV Products/' . SCV_PRODUCTS_VER . '; ' . home_url('/'),
    ]);

    if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $manifest = scv_products_sanitize_update_manifest(
        json_decode(wp_remote_retrieve_body($response), true)
    );
    if (!$manifest) {
        return false;
    }

    set_transient(SCV_PRODUCTS_UPDATE_CACHE_KEY, $manifest, SCV_PRODUCTS_UPDATE_CACHE_TTL);
    return $manifest;
}

/**
 * @param mixed $manifest
 * @return array|false
 */
function scv_products_sanitize_update_manifest($manifest) {
    if (!is_array($manifest)) {
        return false;
    }

    $version = isset($manifest['version']) ? sanitize_text_field((string) $manifest['version']) : '';
    $download_url = isset($manifest['download_url']) ? esc_url_raw((string) $manifest['download_url']) : '';
    if ($version === '' || $download_url === '') {
        return false;
    }

    $scheme = wp_parse_url($download_url, PHP_URL_SCHEME);
    $hostname = wp_parse_url($download_url, PHP_URL_HOST);
    if ($scheme !== 'https' || !is_string($hostname) || $hostname === '') {
        return false;
    }

    $hostname = strtolower($hostname);
    $allowed_hosts = [
        'github.com',
        'objects.githubusercontent.com',
        'release-assets.githubusercontent.com',
    ];
    $host_ok = in_array($hostname, $allowed_hosts, true)
        || substr($hostname, -19) === '.githubusercontent.com';
    if (!$host_ok) {
        return false;
    }

    return [
        'name'         => isset($manifest['name']) ? sanitize_text_field((string) $manifest['name']) : 'SCV Products',
        'slug'         => 'scv-products',
        'version'      => $version,
        'download_url' => $download_url,
        'requires'     => isset($manifest['requires']) ? sanitize_text_field((string) $manifest['requires']) : '6.0',
        'tested'       => isset($manifest['tested']) ? sanitize_text_field((string) $manifest['tested']) : '6.8',
        'requires_php' => isset($manifest['requires_php']) ? sanitize_text_field((string) $manifest['requires_php']) : '7.4',
        'homepage'     => isset($manifest['homepage']) ? esc_url_raw((string) $manifest['homepage']) : 'https://github.com/recordandmix/scv-products',
        'changelog'    => isset($manifest['changelog']) ? (string) $manifest['changelog'] : '',
    ];
}

/**
 * @param array $manifest
 * @return object
 */
function scv_products_build_update_object($manifest) {
    $plugin = scv_products_plugin_basename();
    return (object) [
        'id'           => $plugin,
        'slug'         => 'scv-products',
        'plugin'       => $plugin,
        'new_version'  => $manifest['version'],
        'url'          => $manifest['homepage'],
        'package'      => $manifest['download_url'],
        'icons'        => [],
        'banners'      => [],
        'banners_rtl'  => [],
        'tested'       => $manifest['tested'],
        'requires'     => $manifest['requires'],
        'requires_php' => $manifest['requires_php'],
    ];
}

/**
 * Inject this plugin into WordPress's update transient.
 *
 * @param object|false $transient
 * @return object|false
 */
function scv_products_filter_update_plugins($transient) {
    if (!is_object($transient)) {
        return $transient;
    }

    $force = !empty($_GET['force-check']);
    $manifest = scv_products_get_update_manifest($force);
    if (!$manifest) {
        return $transient;
    }

    $plugin = scv_products_plugin_basename();
    $item = scv_products_build_update_object($manifest);

    if (version_compare($manifest['version'], SCV_PRODUCTS_VER, '>')) {
        $transient->response[$plugin] = $item;
        if (isset($transient->no_update[$plugin])) {
            unset($transient->no_update[$plugin]);
        }
    } else {
        $transient->no_update[$plugin] = $item;
        if (isset($transient->response[$plugin])) {
            unset($transient->response[$plugin]);
        }
    }

    return $transient;
}

/**
 * Populate the "View details" popup for this plugin.
 *
 * @param mixed  $result
 * @param string $action
 * @param object $args
 * @return mixed
 */
function scv_products_plugins_api($result, $action, $args) {
    if ($action !== 'plugin_information' || empty($args->slug) || $args->slug !== 'scv-products') {
        return $result;
    }

    $manifest = scv_products_get_update_manifest();
    if (!$manifest) {
        return $result;
    }

    $changelog = $manifest['changelog'];
    if (function_exists('wp_kses_post')) {
        $changelog = wp_kses_post($changelog);
    }

    return (object) [
        'name'          => $manifest['name'],
        'slug'          => 'scv-products',
        'version'       => $manifest['version'],
        'author'        => '<a href="https://github.com/recordandmix/scv-products">SoCal VoCals Swag</a>',
        'homepage'      => $manifest['homepage'],
        'requires'      => $manifest['requires'],
        'tested'        => $manifest['tested'],
        'requires_php'  => $manifest['requires_php'],
        'download_link' => $manifest['download_url'],
        'sections'      => [
            'description' => 'Custom Product post type, Collections taxonomy, and Always Available product cards block for SoCal VoCals Swag.',
            'changelog'   => $changelog,
        ],
    ];
}

/**
 * Make sure an unpacked update lands in wp-content/plugins/scv-products.
 *
 * GitHub-generated archives often extract to a tagged folder name. Our own
 * release zip already uses scv-products/, but this keeps the plugin active
 * if a different archive is used.
 *
 * @param string      $source
 * @param string      $remote_source
 * @param WP_Upgrader $upgrader
 * @param array       $hook_extra
 * @return string|WP_Error
 */
function scv_products_upgrader_source_selection($source, $remote_source, $upgrader, $hook_extra = []) {
    global $wp_filesystem;

    if (is_wp_error($source)) {
        return $source;
    }

    $plugin = scv_products_plugin_basename();
    if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== $plugin) {
        return $source;
    }

    $desired = trailingslashit($remote_source) . 'scv-products';
    $current = untrailingslashit($source);
    if ($current === $desired) {
        return $source;
    }

    if (!is_object($wp_filesystem) || !method_exists($wp_filesystem, 'move')) {
        return $source;
    }

    if ($wp_filesystem->move($source, $desired, true)) {
        return trailingslashit($desired);
    }

    return new WP_Error(
        'scv_products_upgrade_source',
        'Could not prepare the SCV Products update for installation.'
    );
}
