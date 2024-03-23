<?php

class Tf_Ip_Event
{
    function __construct()
    {
        add_action( 'init', [$this ,'tfIpf_register_event_post_type'] );
        add_action( 'save_post', [$this, 'save_tfIpf_meta_event_box_data'] );

        //add_filter('query_vars', [$this, 'parameter_queryvars'] );

        //add_filter( 'page_link',  [$this, 'add_custom_param_to_template_link'], 10, 3 );
        //add_filter( 'post_type_link', [$this, 'tfIpf_max_booking_tfipfbooking_onevent'], 10, 2 );
    }

   

    // function parameter_queryvars( $qvars ) {
    //     $qvars[] = 'myvar';
    //     return $qvars;
    // }

    // function add_custom_param_to_template_link( $permalink, $post_id, $sample ) {
        

    //     if ( is_page_template( 'tfipfevent' ) ) {
            
    //         $event_date = get_post_meta( $post_id, '_tfIpf_event_date_time', true );
    //         $event_date_formatted = date('Y-m-d', $event_date);
    
    //         $table_name = $wpdb->prefix . 'ipf_bookings';
    
    //         $participants_sum = $wpdb->get_var( 
    //             $wpdb->prepare( 
    //                 "SELECT SUM(participants) 
    //                 FROM $table_name 
    //                 WHERE DATE(date_book) = %s",
    //                 $event_date_formatted
    //             ) 
    //         );
            
    //         $participants_sum == null ? 0 : $participants_sum;
    
    //         $max_participants = get_post_meta( $post_id, '_tfIpf_event_max_participants', true );
    
    //         $available = $max_participants - $participants_sum;


    //         $permalink = add_query_arg( 'available', $available , $permalink );

    //     }
    //     return $permalink;
    // }
    

    function tfIpf_max_booking_tfipfbooking_onevent( $url, $post ) {

        global $wpdb;
    
        $event_date = get_post_meta( $post->ID, '_tfIpf_event_date_time', true );
        $event_date_formatted = date('Y-m-d', $event_date);

        $table_name = $wpdb->prefix . 'ipf_bookings';

        $participants_sum = $wpdb->get_var( 
            $wpdb->prepare( 
                "SELECT SUM(participants) 
                FROM $table_name 
                WHERE DATE(date_book) = %s",
                $event_date_formatted
            ) 
        );
        
        // Fix typo here: $participants_sum instead of $participants
        $participants_sum == null ? 0 : $participants_sum;

        $max_participants = get_post_meta( $post->ID, '_tfIpf_event_max_participants', true );

        $available = $max_participants - $participants_sum;

        
        // global $wp_query;
        // $wp_query->query_vars['available'] = $available;
    }

    function tfIpf_register_event_post_type() {

        $supports = array(
            'title',
            'thumbnail',
            'editor', 
            );

        $labels = array(
            'name' => _x('Eventi', 'plural'),
            'singular_name' => _x('Evento', 'singular'),
            'menu_name' => _x('The Florence Eventi', 'admin menu'),
            'name_admin_bar' => _x('The Florence Eventi', 'admin bar'),
            'add_new' => _x('Aggiungi Evento', 'add new'),
            'add_new_item' => __('Aggiungi Evento'),
            'new_item' => __('Nuovo Evento'),
            'edit_item' => __('Modifica Evento'),
            'view_item' => __('Vedi Evento'),
            'all_items' => __('Tutti gli eventi'),
            'search_items' => __('Cerca Evento'),
            'not_found' => __('Nessun Evento trovato.'),
            
            );
        

        $args = array(
            'supports' => $supports,
            'labels' => $labels,
            'public' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'tfipfevent'),
            'has_archive' => true,
            'hierarchical' => true,
            'register_meta_box_cb' => [$this,  'tfIpf_notice_meta_box'],
        );


        register_post_type( 'tfipfevent' , $args );
    }

    function tfIpf_notice_meta_box()
    {
        $screens = array( 'tfipfevent' );

        foreach ( $screens as $screen ) {
            add_meta_box(
                'tf_ipf_event_metabox',
                __( 'Parametri Evento', 'sitepoint' ),
                [$this, 'tf_ipf_event__meta_box_callback'],
                $screen
            );
        }
    }


    function tf_ipf_event__meta_box_callback( $post ) {

        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'tf_ipf_nonce_global', 'tfIpf_one_once' );

        //$event_description = get_post_meta( $post->ID, '_tfIpf_event_description', true );


        $time_event = date('H:i', get_post_meta( $post->ID, '_tfIpf_event_date_time', true ));
        $date_event = date('Y-m-d',  get_post_meta( $post->ID, '_tfIpf_event_date_time', true ));

        $event_type = get_post_meta($post->ID, '_tfIpf_event_type', true);
        $image_p = get_post_meta( $post->ID, '_tfIpf_event_image', true );
        $teamone = get_post_meta($post->ID, '_tfIpf_event_team_one', true);
        $teamtwo = get_post_meta($post->ID, '_tfIpf_event_team_two', true);
        $bandname = get_post_meta($post->ID, '_tfIpf_event_band_name', true);
        $piatto = get_post_meta($post->ID, '_tfIpf_event_piatto', true);

        $max_participants = get_post_meta($post->ID, '_tfIpf_event_max_participants', true);
        $bookingrecv = 'to be calculated';

        ?>


        <label for="date_event"><?php _e('Date Event (formato data: MESE-GIORNO-ANNO)', 'textdomain'); ?></label><br>
        <div style="display: flex; margin-bottom: 10px;">
            <input style="width: 60%; margin-bottom: 10px;" type="date" id="event_date" name="event_date" value="<?php echo $date_event; ?>"> <!-- Date input -->
            <input style="width: 40%; margin-bottom: 10px;" type="time" id="event_time" name="event_time" value="<?php echo $time_event; ?>"> <!-- Time input -->
        </div>


        <label style="width:100%; margin-top: 10px;" for="type_event"><?php _e('Type Event:', 'textdomain'); ?></label>
        <select style="width:100%; margin-bottom: 10px;" id="type_event" name="type_event">
            <option value="sport" <?php selected($event_type, 'sport'); ?>><?php _e('Sport', 'textdomain'); ?></option>
            <option value="music" <?php selected($event_type, 'music'); ?>><?php _e('Music', 'textdomain'); ?></option>
            <option value="food" <?php selected($event_type, 'food'); ?>><?php _e('Food', 'textdomain'); ?></option>
        </select><br>

        <div id="sport_fields" style="display: <?php echo ($type_event === 'sport') ? 'inline-block' : 'none'; ?>; margin: 10px;">   
            <label for="teamone"><?php _e('Squadra di casa:', 'textdomain'); ?></label>
            <input type="text" id="teamone" name="teamone" value="<?php echo $teamone ?>" style="margin-right: 10px;"> 
            <label for="teamtwo"><?php _e('Squadra trasferta:', 'textdomain'); ?></label>
            <input type="text" id="teamtwo" value="<?php echo $teamtwo ?>" name="teamtwo">
        </div>

        <div id="music_fields" style="display: <?php echo ($type_event === 'music') ? 'block' : 'none'; ?>; margin: 10px;">
            <label style="width:80%" for="bandname"><?php _e('Band Name:', 'textdomain'); ?></label>
            <input style="width:80%" type="text" value="<?php echo $bandname ?>" id="bandname" name="bandname"><br>
        </div>

        <div id="food_fields" style="display: <?php echo ($type_event === 'food') ? 'block' : 'none'; ?>; margin: 10px;">
            <label style="width:80%" for="piatto"><?php _e('Piatto Forte:', 'textdomain'); ?></label>
            <input style="width:80%" type="text" value="<?php echo $piatto ?>" id="piatto" name="piatto"><br>
        </div>

        <!-- <div style="display: flex; flex-direction: column; margin-bottom: 10px;">
            <label for="description_event"><?php _e('Description Event:', 'textdomain'); ?></label>
            <textarea id="description_event" style="width:100%; height: 200px; margin-bottom: 10px;" name="description_event"><?php echo esc_textarea($event_description); ?></textarea><br>
        </div> -->

        <div style="display: flex; flex-direction: column; margin-bottom: 10px;">
            <label for="maxnum"><?php _e('Numero Massimo Prenotazioni: ', 'textdomain'); ?></label>
            <input type="text" id="maxnum" name="maxnum" value="<?php echo esc_attr($max_participants); ?>" style="margin-bottom: 10px;">

            <label for="bookingrecv"><?php _e('Prenotazioni Ricevute:', 'textdomain'); ?></label>
            <input type="text" id="bookingrecv" name="bookingrecv" value="<?php echo esc_attr($bookingrecv); ?>" readonly>
        </div>

    
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var typeEventSelect = document.getElementById('type_event');
                var sportFields = document.getElementById('sport_fields');
                var musicFields = document.getElementById('music_fields');
                var foodFields = document.getElementById('food_fields');


                function toggleFields() {
                    var selectedType = typeEventSelect.value;

                    sportFields.style.display = (selectedType === 'sport') ? 'block' : 'none';
                    musicFields.style.display = (selectedType === 'music') ? 'block' : 'none';
                    foodFields.style.display = (selectedType === 'food') ? 'block' : 'none';
                }

                toggleFields();


                typeEventSelect.addEventListener('change', toggleFields);
            });

            jQuery(document).ready(function($) {
                var customUploader;

                $('#upload_event_image_button').click(function(e) {
                    e.preventDefault();

                    if (customUploader) {
                        customUploader.open();
                        return;
                    }

                    customUploader = wp.media.frames.file_frame = wp.media({
                        title: '<?php _e('Choose Image', 'textdomain'); ?>',
                        button: {
                            text: '<?php _e('Choose Image', 'textdomain'); ?>'
                        },
                        multiple: false
                    });

                    customUploader.on('select', function() {
                        var attachment = customUploader.state().get('selection').first().toJSON();
                        $('#event_image_url').val(attachment.url);
                        $('#event_image_attachment_id').val(attachment.id);
                    });

                    customUploader.open();
                });
            });
        </script>

        <?php

        // echo '<textarea style="width:50%" id="event_description" name="event_description">' . esc_attr( $value ) . '</textarea>';
    }

    function save_tfIpf_meta_event_box_data( $post_id ) {

        // Check if our nonce is set.
        // if ( ! isset( $_POST['global_notice_nonce'] ) ) {
        //     return;
        // }

        // // Verify that the nonce is valid.
        // if ( ! wp_verify_nonce( $_POST['global_notice_nonce'], 'global_notice_nonce' ) ) {
        //     return;
        // }




        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }

        }
        else {

            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        /* OK, it's safe for us to save the data now. */

        // Make sure that it is set.
        // if ( ! isset( $_POST['event_description'] ) ) {

        //     return;
        // }
        


        // Sanitize user input.
        //$event_description = sanitize_text_field( $_POST['description_event'] );
        $date_event =  strtotime($_POST['event_date'] . " ". $_POST['event_time']);
        //$time_event =  $_POST['event_time'];
        $event_type = $_POST['type_event'];
        $maxnum = $_POST['maxnum'];
        $bandname = $_POST['bandname'];
        $piatto = $_POST['piatto'];
        $teamone = $_POST['teamone'];
        $teamtwo = $_POST['teamtwo'];
        
        

        // Update the meta field in the database.
        //update_post_meta( $post_id, '_tfIpf_event_description', $event_description );

        update_post_meta( $post_id, '_tfIpf_event_date_time', $date_event);
        //update_post_meta( $post_id, '_tfIpf_event_time', date('H:i', strtotime($time_event)) ); //strtotime(date('H:i', strtotime($time_event))) );
        update_post_meta( $post_id, '_tfIpf_event_type', $event_type );
        update_post_meta( $post_id, '_tfIpf_event_team_one', $teamone );
        update_post_meta( $post_id, '_tfIpf_event_team_two', $teamtwo );
        update_post_meta( $post_id, '_tfIpf_event_band_name', $bandname );
        update_post_meta( $post_id, '_tfIpf_event_piatto', $piatto );
        update_post_meta( $post_id, '_tfIpf_event_max_participants', $maxnum );

        
    }

   

}