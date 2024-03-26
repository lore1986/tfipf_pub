<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Tf_Ipf_Database {


    
    function __construct()
    {
       
    }
    
    public function PrintErrorMessage($message)
    {
        return '<h4 style="color:red;"> There is an error: ' . $message .' please contact administrators<h4>';
    }

    public function tfIpf_get_date($id) {
        
        global $wpdb;

        $table_name = $wpdb->prefix . 'ipf_days_date';

        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
        $result = $wpdb->get_row($query);

        if ($result !== null) {
            return $result;
        } else {
            return false;
        }
    }

    public function tfIpf_update_days_date_bookings($id, $newBookings) {
        
        global $wpdb;

        $table_name = $wpdb->prefix . 'ipf_days_date';
    
        $currentBookings = $wpdb->get_var($wpdb->prepare("SELECT bookings FROM $table_name WHERE id = %d", $id));
        $maxParticipants = $wpdb->get_var($wpdb->prepare("SELECT max_participants FROM $table_name WHERE id = %d", $id));

        $updatedBookings = $currentBookings + $newBookings;


        if ($updatedBookings > $maxParticipants) {
            return false;
        }
        
        $data = array(
            'bookings' => $updatedBookings,
        );
    
        $where = array(
            'id' => $id,
        );
    
        $wpdb->update($table_name, $data, $where);
    
        $rows_affected = $wpdb->rows_affected;
        
        if ($rows_affected > 0) {
            return true;
        } else {
            return false;
        }
    }
    

   

    public function tfIpf_filter_events()
    {
        if(isset($_POST['usersearch'])) {

            $searchText = sanitize_text_field($_POST['usersearch']);
            $searchText = strtolower($searchText);
    
            $query_tfIpf = new WP_Query(
                array(
                    's' => $searchText,
                    'post_type' => 'tfipfevent',
                    'post_status' => 'publish',
                )
            );
            
    
            // Output the filtered events as options for select
            if ($query_tfIpf->have_posts()) {
                while ($query_tfIpf->have_posts()) {

                    $query_tfIpf->the_post();
                    $pid = get_the_ID();
                    $date_event_timestamp = get_post_meta( $pid, '_tfIpf_event_date_time', true );

                    if(!empty($date_event_timestamp))
                    {
                        $date_event = date('d/m/Y', $date_event_timestamp);
                    }

                    echo'<option value="' . $pid . '">' . get_the_title() . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Data Evento: ' . $date_event . '</span></option>';

                }
                wp_reset_postdata();
            } else {
                echo '<option>No events found</option>';
            }

            echo '<option value="-1"> Tavolo </span></option>';
        }
    
        wp_die();
    }

    public function tfIpf_event_query_list($maxnum = -1)
    {
    
        $timestamp_now = strtotime(date('Y-m-d'));

        $events = array();

        $query_tfIpf = new WP_Query(
            array(
                'post_type'      => 'tfipfevent',
                'post_status'    => 'publish',
                'posts_per_page' => $maxnum,
                'meta_query'     => array(
                    array(
                        'key'     => '_tfIpf_event_date_time',
                        'value'   => $timestamp_now,
                        'type'    => 'NUMERIC',
                        'compare' => '>='
                    )
                )
            )
        );

        while($query_tfIpf->have_posts())
        {
            $query_tfIpf->the_post();

            $the_single_ipf = new stdClass();
            $the_single_ipf->id = get_the_ID();
            $the_single_ipf->title = get_the_title($the_single_ipf->id);
            
            $the_single_ipf->event_description = get_post_meta( $the_single_ipf->id, '_tfIpf_event_description', true );
            
            $the_single_ipf->time_event = date('H:i', get_post_meta( $the_single_ipf->id, '_tfIpf_event_date_time', true ));
            $the_single_ipf->date_event = date('Y-m-d',  get_post_meta( $the_single_ipf->id, '_tfIpf_event_date_time', true ));

            $the_single_ipf->event_type = get_post_meta($the_single_ipf->id, '_tfIpf_event_type', true);
            $the_single_ipf->image_p = get_post_meta( $the_single_ipf->id, '_tfIpf_event_image', true );
            $the_single_ipf->teamone = get_post_meta($the_single_ipf->id, '_tfIpf_event_team_one', true);
            $the_single_ipf->teamtwo = get_post_meta($the_single_ipf->id, '_tfIpf_event_team_two', true);
            $the_single_ipf->max_participants = get_post_meta($the_single_ipf->id, '_tfIpf_event_max_participants', true);

            $the_single_ipf->available = "to be calculated";

            array_push($events, $the_single_ipf);
        }

        wp_reset_query();

        return $events;
    }



    

    public function tfIpf_verify_code($id, $usercode)
    {
        $return_arr = array();

        global $wpdb;
        $table_name = $wpdb->prefix . 'ipf_bookings'; 

        $booking_code = $wpdb->get_var(
            $wpdb->prepare("SELECT code FROM $table_name WHERE id = %d", $id)
        );

        if ($booking_code !== null && $usercode == $booking_code) {
            
            
            $new_status = 'confirmed';

            $result = $wpdb->update(
                $table_name,
                array('status' => $new_status),
                array('id' => $id),
                array('%s'), 
                array('%d') 
            );

            if ($result !== false) {
                
                $return_arr = array('error' => 0);

            } else {

                $return_arr = array('error' => 1, 'error_message' => 'error updating booking');
            }

        } else {

            $return_arr = array('error' => 2, 'error_message' => 'booking does not exist');
        }

        return $return_arr;
    }



    public function tfIpf_generate_code()
    {
        $arr_charN = 'qwertyuiopasdfghjklzxcvbnm1234567890';
        $code = '';
     
        for ($x = 0; $x < 6; $x++) {
            $rand_ind = rand(0, strlen($arr_charN) - 1); // Subtract 1 to get a valid index
            $code .= $arr_charN[$rand_ind];
        }
        
    
        return mb_strtoupper($code);
    }


    // public function save_tfIpf_booking($booking) {
        
    //     global $wpdb;
    //     $table_name = $wpdb->prefix . 'ipf_bookings';

    //     if(!empty($booking->identification) && !empty($booking->phone))
    //     {
           
    //         $code = $this->tfIpf_generate_code($booking);
    
    //         $post_data = array(
    //             'post_title' => $code,
    //             'post_type'   => 'tfipfbooking',
    //             'post_status' => 'publish'
    //         );
            
    //         $post_id = wp_insert_post($post_data);
            
    //         if (is_wp_error($post_id)) {

    //             $data = array('error'=>'1', 'error_message' =>"error creating post");
    //             return $data;
    //         }

    //         $convd = date('Y-m-d H:i:s', intval($booking->date));
    //         $booking->code = substr($code, 4);
        
    //         $data = array(
    //             'date_book' =>  $convd,
    //             'post_booking_id' => $post_id,
    //             'post_event_id' => $booking->post_event_id,
    //             'identification' => $booking->identification,
    //             'participants' => $booking->participants,
    //             'phone' => $booking->phone,
    //             'email' => $booking->email,
    //             'extra_message' => $booking->extra_message,
    //             'code' => $booking->code,
    //             'status' => 'forwarded'
    //         );
        
    //         $result = $wpdb->insert(
    //             $table_name,
    //             $data,
    //             array('%s','%d', '%d', '%s', '%d', '%s', '%s', '%s','%s','%s')
    //         );
        
    //         if ($result === false) {
    //             $error_message = $wpdb->last_error;
    //             $data = array('error'=>'1', 'error_message' =>"error inserting data into database");
    //             return $data;
    //         }
            
    //         $inserted_id = $wpdb->insert_id;
    //         $data = array('error'=>'0', 'data' => $booking, 'id' => $inserted_id);
    //         return $data;

    //     }else
    //     {
    //         $data = array('error'=>'1', 'error_message' =>"missing phone or identification in form");
    //         return $data;
    //     }


       
    // }


    function ipf_save_edit_event($event)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ipf_events';

        $event_id = $event->event_id;

        $result = $wpdb->update(
            $table_name,
            array(
                'event_title' => $event->event_title,
                'date_event' =>  $event->date_event,
                'type_event' => $event->event_type,
                'maxnum' => $event->eventPlaces,
                'description_event' => $event->event_description,
                'image_path' =>  $event->image_url,
            ),
            array('event_id' => $event_id), // Update based on event ID
            array('%s', '%s', '%s', '%d', '%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            $error_message = $wpdb->last_error;
            echo "Error updating data: $error_message";
            return false;
        } else {
            return true;
        }

    }


    public function tfIpf_query_booking_on_date($date)
    {
        global $wpdb;

        $date_stamp = strtotime($date);

        $query = $wpdb->prepare("
            SELECT COUNT(*) AS total_bookings
            FROM {$wpdb->prefix}ipf_bookings
            WHERE date_id >= %d AND date_id < %d
        ", $date_stamp, $date_stamp + 86400); 

        $totalBookings = $wpdb->get_var($query);

        if ($totalBookings === null) {
            $totalBookings = 0;
        }

        return $totalBookings;
    }



    public function tfIpf_create_dbtable() {

        global $wpdb;

        $table_name = $wpdb->prefix . 'ipf_days_date';
        $sql = "SHOW TABLES LIKE '$table_name'";
    

        $table_exists = $wpdb->get_var($sql);

        if ($table_exists != $table_name) {
            
            $table_name = $wpdb->prefix . 'ipf_days_date';
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id BIGINT PRIMARY KEY NOT NULL,
                bookings INT NOT NULL,
                max_participants INT NOT NULL
            )";

            // Execute the query
            $wpdb->query($sql);

            $year = date('Y'); 
            $num_days = date('z', mktime(0, 0, 0, 12, 31, $year)) + 1;
            $table_rows = ''; 

            for ($day = 1; $day <= $num_days; $day++) {

                $timestamp = mktime(0, 0, 0, 1, $day, $year);
            
                $sql = $wpdb->prepare("
                    INSERT INTO {$wpdb->prefix}ipf_days_date (id, bookings, max_participants) 
                    VALUES (%d, 0, 45)
                ", $timestamp);
            
                $wpdb->query($sql);
            }
            
        } else {
            // Table already exists
            echo "Table already exists!";
        }

    
        $table_name = $wpdb->prefix . 'ipf_bookings';
        $sql = "SHOW TABLES LIKE '$table_name'";
    
        // Execute the query
        $table_exists = $wpdb->get_var($sql);
    
        if ($table_exists != $table_name) {
            
            $charset_collate = $wpdb->get_charset_collate();
    
            $sql_booking = "CREATE TABLE IF NOT EXISTS $table_name (
                `id` int NOT NULL AUTO_INCREMENT,
                `post_event_id` int DEFAULT NULL,
                `identification` varchar(255) NOT NULL,
                `participants` int NOT NULL,
                `phone` varchar(20) NOT NULL,
                `extra_message` text,
                `code` varchar(6) NOT NULL,
                `status` enum('forwarded', 'confirmed', 'cancelled') NOT NULL,
                `time_booking` time DEFAULT NULL,
                `date_id` bigint DEFAULT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`date_id`) REFERENCES " . $wpdb->prefix . "ipf_days_date(`id`)
            ) $charset_collate;";
                        
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql_booking); 
        } else {
            // Table already exists
            echo "Table already exists!";
        }
          
    }


    function tfIpf_query_bookings_on_date_noCount($timestamp)
    {
        global $wpdb;


        $query = $wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}ipf_bookings
            WHERE date_id >= %d AND date_id < %d
        ", $timestamp, $timestamp + 86400); 
        
        $bookings = $wpdb->get_results($query);

        return $bookings;
    }

    
    

}