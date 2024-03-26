<?php

/**
 * Plugin Name: The Florence Irish Pub
 * Description: The Florence Irish Pub Firenze Booking
 * Version: 1.0
 * Author: Tripleg
 */

include_once(plugin_dir_path(__DIR__ ) . 'tfIp_Pub/classes/TfIpfCalendar.php');
include_once(plugin_dir_path(__DIR__ ) . 'tfIp_Pub/classes/TfIpfDatabase.php');
include_once(plugin_dir_path(__DIR__ ) . 'tfIp_Pub/classes/TfIpfEvents.php');
include_once(plugin_dir_path(__DIR__ ) . 'tfIp_Pub/classes/TfIpfBookings.php');
include_once(plugin_dir_path(__DIR__ ) . 'tfIp_Pub/classes/TfIpfManager.php');
include_once(plugin_dir_path(__DIR__ ) . 'tfIp_Pub/classes/TfIpfAdmin.php');


register_activation_hook( __FILE__, 'tf_ipf_registration_handler' );
register_deactivation_hook( __FILE__, 'tf_ipf_deregistration_handler' );

$database = new Tf_Ipf_Database();
$calendar = new Tf_Ipf_Calendar($database);
$event = new Tf_Ip_Event();
$manager  = new Tf_Ipf_Manager();
$bookings = new Tf_Ipf_Booking($database, $manager);
$admin = new Tf_Ipf_Admin();



function tf_ipf_registration_handler()
{
    $pages = new TfIpf_Pages();
    $pages->create_pages();

    $first_data = new Tf_Ipf_Database();
    $first_data->tfIpf_create_dbtable();

}

function tf_ipf_deregistration_handler()
{
    //delete table
    //delete template
    //delete everything was created
}



add_action( 'plugins_loaded', array( 'TfIpf_Templater', 'get_instance' ) );
add_action('wp_enqueue_scripts', 'tf_ipf_enqueue_scripts');

add_action( 'wp_ajax_get_calendar_html', array( $calendar, 'get_calendar_html'));
add_action( 'wp_ajax_nopriv_get_calendar_html', array( $calendar, 'get_calendar_html'));

add_action( 'wp_ajax_tf_ipf_get_admin_calendar', array( $calendar, 'get_admin_calendar'));
add_action( 'wp_ajax_tf_ipf_get_day_bookings', array( $calendar, 'get_day_bookings'));

add_action( 'wp_ajax_tfIpf_book_check_one', array( $bookings, 'tfIpf_return_booking_date_form'));
add_action( 'wp_ajax_nopriv_tfIpf_book_check_one', array( $bookings, 'tfIpf_return_booking_date_form'));

add_action( 'wp_ajax_tf_ipf_create_booking', array( $bookings, 'tfIpf_create_booking_confirm_booking'));
add_action( 'wp_ajax_nopriv_tf_ipf_create_booking', array( $bookings, 'tfIpf_create_booking_confirm_booking'));

add_action( 'wp_ajax_tf_ipf_confirm_booking', array( $bookings, 'tfIpf_final_booking_confirm'));
add_action( 'wp_ajax_nopriv_tf_ipf_confirm_booking', array( $bookings, 'tfIpf_final_booking_confirm'));



add_action( 'wp_ajax_tf_ipf_filter_events', array( $database, 'tfIpf_filter_events'));

add_action( 'wp_ajax_tfipf_return_edit_booking_form_ajax', array( $calendar, 'tfipf_return_edit_booking_form_ajax'));
add_action( 'wp_ajax_ifpsave_edit_booking', array( $calendar, 'ifpsave_edit_booking'));




add_shortcode( 'tfIpfCalendarShort', 'tfIpf_calendar_all_event_shortcode' );
add_shortcode( 'tfIpfNoEventBooking', 'tfIpf_noEvent_booking_shortcode' );

$plugin = plugin_basename( __FILE__ );
//add_filter( "plugin_action_links_$plugin", [$admin, 'plugin_add_settings_link'] );
add_filter( "plugin_action_links_$plugin", [$admin,'tfipf_add_settings_link'] );

function tfIpf_calendar_all_event_shortcode($atts)
{
    $max_num = isset($atts['maxnum']) ? $atts['maxnum'] : -1;

    $html = '<div class="row">
                <div id="container-booking" class="col-12" style="display:none;">
                </div>
                <div id="container-list-events" class="col-12">
                </div>
            </div>';
    $html .= '
            <script>
                window.onload = function() {
                    ajax_call_calendar(' . $max_num . ');
                };
            </script>
        ';

    return $html;
    exit();
}

function tfIpf_noEvent_booking_shortcode()
{
    return '
    <style>
    // .bootstrap-datetimepicker-widget{
    //     display:block;
    // }
    </style><div class="calendario-prenotazione">
                <div id="container-booking">
                    <div class="relative-container" style="position: relative;" >
                        <input type="text" id="client_date"  class="dropdown" name="client_date"/>
                    </div>
                    <div class="relative-container" style="position: relative;" >
                        <input type="text" id="client_time" name="client_time" />
                    </div>
                    <button type="button" id="button-no-event-booking" onclick="BookNoEvent()" class="btn btn-success">Prenota</button>
                </div>
                <div id="error-booking-noevent">
                </div>
            </div>
            <script>
            jQuery(document).ready(function($) {

                $(\'#client_time\').datetimepicker({
                    format: \'H:mm\',
                });
                
                $(\'#client_date\').datetimepicker({
                    format: \'DD MMM YYYY\',
                    viewMode: \'days\',
                    widgetPositioning: {
                        vertical: \'bottom\', // always opens to buttom direction
                    },
                    minDate: moment(),
                    defaultDate: moment(), 
                });
            });

            </script>';
}


function tf_ipf_enqueue_scripts() {

    //wp_enqueue_script('jquery');
    //wp_enqueue_script('moment-js', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js', array(), '2.15.1', true);

    // Enqueue Bootstrap 
    //wp_enqueue_script('bootstrap-js', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), '3.3.7', true);
    //wp_enqueue_style('bootstrap-css', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css', array(), '3.3.7');

    // Enqueue Bootstrap DateTimePicker JavaScript
    // wp_enqueue_script('bootstrap-datetimepicker-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/js/bootstrap-datetimepicker.min.js', array('jquery', 'moment-js'), '4.7.14', true);
    // wp_enqueue_style('bootstrap-datetimepicker-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.min.css', array(), '4.7.14');

    //wp_enqueue_style('bootstrap-glyphicons', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css');

    wp_register_script( 'bootstrap-datepicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js', array( 'jquery' ), '1.3.0', true );
    wp_enqueue_script( 'bootstrap-datepicker' );
        
    wp_enqueue_style('intlTelInput', 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/css/intlTelInput.css');
    wp_enqueue_script('intlTelInput', 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/intlTelInput.min.js', array('jquery'), null, true);

    wp_enqueue_script('tf_ipf_booking_event_js',  plugin_dir_url(__FILE__) . 'static/js/event_booking.js', array('jquery'));
    wp_enqueue_script('tf_ipf_calendar_js',  plugin_dir_url(__FILE__) . 'static/js/calendar_js.js', array('jquery'), '1.0.0', true );
    wp_localize_script('tf_ipf_calendar_js', 'ajaxurl', admin_url('admin-ajax.php'));


}

class TfIpf_Pages{

    function __construct()
    {

    }
    
    public function create_pages() {
        $titles = array();
        $titles[0] = 'Prenotazioni';


        foreach ($titles as $t) {
            $args = array(
                'post_type' => 'page',
                'pagename' => $t
            );

            $content = '';
            $query = new WP_Query($args);

            if (!$query->have_posts()) {
                $pgg = array(
                    'post_title'   => $t,
                    'post_content' => $content,
                    'post_status'  => 'private',
                    'post_type'    => 'page',
                );

                $insert_page = wp_insert_post($pgg);
            }
        }
    }
}

class TfIpf_Templater
{
    private static $templater_instance;
    protected $templates;

    public static function get_instance()
    {
        if ( null == self::$templater_instance ) {
			self::$templater_instance = new TfIpf_Templater();
		}

		return self::$templater_instance;
    }

    function __construct()
    {
        $this->templates = array();

        add_filter( 'theme_page_templates', array($this, 'tfIpf_add_template')); //
        add_filter('wp_insert_post_data', array($this, 'tfIpf_register_template'));
        add_filter('template_include', array($this, 'tfIpf_check_template'));

        $this->templates[0] = array('tfIpf_event.php' => 'Evento');
        $this->templates[1] = array('tfIpf_admin_booking.php' => 'Prenotazioni');
    }

    public function tfIpf_add_template($post_templates)
    {
        foreach ($this->templates as $single_t)
        {
            $post_templates = array_merge($post_templates, $single_t);
        }

        return $post_templates;
    }

    public function tfIpf_register_template($atts)
    {
        $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());

        $templates = wp_get_theme()->get_page_templates();

        if(empty($templates))
        {
            $templates = array();
        }

        wp_cache_delete( $cache_key, 'themes' );

        foreach ($this->templates as $single_t)
        {
            $templates = array_merge($templates, $single_t);
            wp_cache_add( $cache_key, $templates, 'themes', 1800 );
        }

        return $atts;
    }

    public function tfIpf_check_template($template)
    {
        global $post;
        if(!$post)
        {
            return $template;
        }

        $hey = get_post_meta($post->ID, '_wp_page_template', true);

        if(is_page("Prenotazioni"))
        {
            $file = plugin_dir_path( __FILE__ ) . 'template/tfIpf_admin_booking.php';
            return $file;

        }else if('tfipfevent' == get_post_type())
        {
            $file = plugin_dir_path( __FILE__ ) . 'template/tfIpf_event.php';
            return $file;
        }


        return $template;
    }
}
