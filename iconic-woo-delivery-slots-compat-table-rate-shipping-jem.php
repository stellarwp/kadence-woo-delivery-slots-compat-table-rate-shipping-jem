<?php
/**
 * Plugin Name:     WooCommerce Delivery Slots by Kadence [Table Rate Shipping PRO by JEM]
 * Plugin URI:      https://iconicwp.com/products/woocommerce-delivery-slots/
 * Description:     Compatibility between WooCommerce Delivery Slots by Kadence and Woocommerce Table Rate Shipping PRO by JEM.
 * Author:          Kadence
 * Author URI:      https://www.kadencewp.com/
 * Text Domain:     iconic-compat-87836
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Iconic_Compat_87836
 */


/**
 * Is table rate shipping active?
 *
 * @return bool
 */
function iconic_compat_87836_is_active() {
	return defined( 'JEM_TRPRO_DOMAIN' );
}

/**
 * Change format of the shipping method ID.
 *
 * @return array
 */
function iconic_compat_87836_update_shipping_method_id( $shipping_method_options ) {
	if ( ! iconic_compat_87836_is_active() ) {
		return $shipping_method_options;
	}

	$table_rate_pro_shipping_methods = array();

	foreach ( $shipping_method_options as $method_key => $method_name ) {
		if ( false === strpos( $method_key, 'jem_table_rate_shipping_method_pro:' ) ) {
			continue;
		}

		$instance_id = str_replace( 'jem_table_rate_shipping_method_pro:', '', $method_key );

		unset( $shipping_method_options[ $method_key ] );

		$table_rate_pro_shipping_methods = $table_rate_pro_shipping_methods + iconic_compat_87836_get_zone_methods( $instance_id );
	}

	$shipping_method_options = $shipping_method_options + $table_rate_pro_shipping_methods;

	return $shipping_method_options;
}

add_filter( 'iconic_wds_shipping_method_options', 'iconic_compat_87836_update_shipping_method_id', 10 );

/**
 * Undocumented function
 *
 * @param int $instance_id ID of the shipping method.
 *
 * @return array
 */
function iconic_compat_87836_get_zone_methods( $instance_id ) {
	global $wpdb;

	$option_key      = sprintf( 'jem_table_rate_pro_shipping_methods_%d', $instance_id );
	$methods         = get_option( $option_key );
	$updated_methods = array();

	$shipping_class_names = WC()->shipping->get_shipping_method_class_names();

	$shipping_zone_title = $wpdb->get_var(
		$wpdb->prepare(
			"select zone_name from {$wpdb->prefix}woocommerce_shipping_zone_methods zm, {$wpdb->prefix}woocommerce_shipping_zones z
			where zm.zone_id = z.zone_id
			and zm.instance_id = %d",
			$instance_id
		)
	);

	foreach ( $methods as $method ) {
		$method_key                     = sprintf( 'jem_table_rate_pro_%d_%s', $instance_id, sanitize_title( $method['method_title'] ) );
		$method_title                   = $shipping_zone_title . ': ' . $method['method_title'] . ' (Table Rate Pro)';
		$updated_methods[ $method_key ] = $method_title;
	}

	return $updated_methods;
}
