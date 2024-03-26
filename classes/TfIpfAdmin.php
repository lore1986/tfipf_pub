<?php

include_once dirname( __FILE__ ) . '/TfIpfDatabase.php';
include_once dirname( __FILE__ ) . '/TfIpfManager.php';

class Tf_Ipf_Admin {
    
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'tfipf_settings_page' ) );
        add_filter( 'plugin_action_links_tfipfpub', array( $this, 'tfipf_add_settings_link' ) );
        add_action( 'admin_init', array( $this, 'tfipf_register_settings' ) );
        add_action( 'admin_init', array( $this, 'tfipf_settings_fields' ) );
    }

    public function tfipf_add_settings_link( $links ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=tfipfpub_settings' ) . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function tfipf_settings_page() {
        add_options_page( 'The Florence Impostazioni', 'TheFlorence Impostazioni', 'manage_options', 'tfipfpub_settings', array( $this, 'tfipf_settings_page_content' ) );
    }

    public function tfipf_settings_page_content() {
        ?>
        <div class="wrap">
            <h2>Impostazioni</h2>
            <form method="post" action="options.php">
                <?php settings_fields( 'tfipf_settings_group' ); ?>
                <?php do_settings_sections( 'tfipfpub_settings' ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function tfipf_register_settings() {
        register_setting( 'tfipf_settings_group', 'tfipf_whatsapp_token' );
        register_setting( 'tfipf_settings_group', 'tfipf_default_capienza', [$this, 'tfipf_sanitize_capienza_value'] );
    }


    public function tfipf_settings_fields() {
        add_settings_section( 'tfipf_settings_section', 'Plugin Settings', '', 'tfipfpub_settings' );
        add_settings_field( 'tfipf_whatsapp_token', 'Token Whatsapp', array( $this, 'tfipf_whatsapp_token_callback' ), 'tfipfpub_settings', 'tfipf_settings_section' );
        add_settings_field( 'tfipf_default_capienza', 'Default Capienza', array( $this, 'tfipf_default_capienza_callback' ), 'tfipfpub_settings', 'tfipf_settings_section' );
    }

    public function tfipf_whatsapp_token_callback() {
        $string_option_value = get_option( 'tfipf_whatsapp_token' );
        echo '<input type="text" name="tfipf_whatsapp_token" value="' . esc_attr( $string_option_value ) . '" />';
    }

    public function tfipf_default_capienza_callback() {
        $number_option_value = get_option( 'tfipf_default_capienza' );
        echo '<input type="number" name="tfipf_default_capienza" value="' . esc_attr( $number_option_value ) . '" />';
    }

    function tfipf_sanitize_capienza_value( $input ) {
        
        global $wpdb;

        $sanitized_capienza = intval( $input );
        $current_timestamp = strtotime('today');

        $six_months_timestamp = strtotime('+6 months', $current_timestamp);

        $existing_records = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ipf_days_date WHERE id >= %d", $current_timestamp));

        if ($existing_records) {

            foreach ($existing_records as $record) {
                $wpdb->update(
                    "{$wpdb->prefix}ipf_days_date",
                    array('max_participants' => $sanitized_capienza),
                    array('id' => $record->id),
                    array('%d'),
                    array('%d')
                );
            }
        } else {

            for ($timestamp = $current_timestamp; $timestamp <= $six_months_timestamp; $timestamp += 86400) { // Increment by 1 day (86400 seconds)
                $wpdb->insert(
                    "{$wpdb->prefix}ipf_days_date",
                    array('id' => $timestamp, 'bookings' => 0, 'max_participants' => $sanitized_capienza),
                    array('%d', '%d', '%d')
                );
            }
        }
        
        return $sanitized_capienza;
    }
}
