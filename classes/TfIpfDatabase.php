<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Tf_Ipf_Database {


    
    function __construct()
    {
       
    }

    function tfIpf_delete_booking($postid)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ipf_bookings';

        $booking_exists = $wpdb->get_var( 
            $wpdb->prepare( 
                "SELECT COUNT(*) FROM $table_name WHERE post_booking_id = %d",
                $postid
            ) 
        );
    
        // If booking exists, delete it
        if ($booking_exists) {
        
            $wpdb->query( 
                $wpdb->prepare( 
                    "DELETE FROM $table_name WHERE post_booking_id = %d",
                    $postid
                ) 
            );

            return true;
        }

        return false;
    }
       
    function ipf_get_event_and_booking($event_id)
    {
        $event_and_booking = new StdClass();

        global $wpdb;
        $table_name = $wpdb->prefix . 'ipf_events';
        $query = $wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE event_id = %d
        ", $event_id);

        $results = $wpdb->get_results($query);        

        $query_booking = $wpdb->prepare("
            SELECT attendees
            FROM {$wpdb->prefix}ipf_bookings
            WHERE event_id = %d AND status = %s
        ", $event_id, 'forwarded');

        $booking_results = $wpdb->get_results($query_booking);

        $total_booking = 0;

        foreach($booking_results as $att)
        {   
            $total_booking += intval($att->attendees);
        }

        $event_and_booking->event = $results[0];
        $event_and_booking->booked = $total_booking;
        $event_and_booking->available = intval($results[0]->maxnum) - $total_booking;

        
        return $event_and_booking;
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
            $the_single_ipf->bandname = get_post_meta($the_single_ipf->id, '_tfIpf_event_band_name', true);
            $the_single_ipf->piatto = get_post_meta($the_single_ipf->id, '_tfIpf_event_piatto', true);
            $the_single_ipf->max_participants = get_post_meta($the_single_ipf->id, '_tfIpf_event_max_participants', true);

            $the_single_ipf->available = "to be calculated";

            array_push($events, $the_single_ipf);
        }

        wp_reset_query();

        return $events;
    }

    public function tfipf_check_exist_and_execute($booking)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ipf_bookings'; 


        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE post_booking_id = %d", $booking->post_id);
        $count = $wpdb->get_var($query);


        if ($count > 0) {
            $ret = $this->tfIpf_update_booking($booking);
            return $ret;
        } else {
            $ret = $this->tfIpf_save_booking_admin($booking);
            return $ret;
        }
    }

    public function tfIpf_update_booking($booking)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ipf_bookings'; 

        $data = array(
            'date_book' =>  $booking->date,
            'post_booking_id' => $booking->post_id,
            'post_event_id' => $booking->post_event_id,
            'identification' => $booking->identification,
            'participants' => $booking->participants,
            'phone' => $booking->phone,
            'email' => $booking->email,
            'extra_message' => $booking->extra_message,
            'code' => $booking->code,
            'status' => $booking->status
        );

        $where = array(
            'post_booking_id' => $id, // Assuming 'id' is the primary key column
        );
        
        // Update data
        $result = $wpdb->update(
            $table_name,
            $data,
            $where
        );

        if ($result === false) {
            // Error occurred
            echo "Error: " . $wpdb->last_error;
            return 0;
        } else {
            // Update successful
            echo "Element updated successfully.";
            return 1;
        }

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

    public function tfIpf_save_booking_admin($booking)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ipf_bookings'; 

        $data = array(
            'date_book' =>  $booking->date,
            'post_booking_id' => $booking->post_id,
            'post_event_id' => $booking->post_event_id,
            'identification' => $booking->identification,
            'participants' => $booking->participants,
            'phone' => $booking->phone,
            'email' => $booking->email,
            'extra_message' => $booking->extra_message,
            'code' => $booking->code,
            'status' => $booking->status
        );

        $format = array('%s','%d', '%d', '%s', '%d', '%s', '%s', '%s','%s','%s');
        
        // Insert data
        $result = $wpdb->insert(
            $table_name,
            $data,
            $format
        );

        if ($result === false) {
            echo "Error: " . $wpdb->last_error;
            return 0;
        } else {
            echo "Element updated successfully.";
            return 1;
        }
    }

    public function tfIpf_generate_code($booking)
    {
        $arr_charN = 'qwertyuiopasdfghjklzxcvbnm1234567890';
            
        $identification = str_replace(' ', '', $booking->identification);
        $identification = strtoupper($identification);
        $identification = substr($identification, 0, 3);


        $code = '';
        $identification .= '_';
        
        for ($x = 0; $x < 6; $x++) {
            $rand_ind = rand(0, strlen($arr_charN) - 1); // Subtract 1 to get a valid index
            $code .= $arr_charN[$rand_ind];
        }

        $identification .= $code;

        return $identification ;
    }


    public function save_tfIpf_booking($booking) {
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ipf_bookings';

        if(!empty($booking->identification) && !empty($booking->phone))
        {
           
            $code = $this->tfIpf_generate_code($booking);
    
            $post_data = array(
                'post_title' => $code,
                'post_type'   => 'tfipfbooking',
                'post_status' => 'publish'
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {

                $data = array('error'=>'1', 'error_message' =>"error creating post");
                return $data;
            }

            $convd = date('Y-m-d H:i:s', intval($booking->date));
            $booking->code = substr($code, 4);
        
            $data = array(
                'date_book' =>  $convd,
                'post_booking_id' => $post_id,
                'post_event_id' => $booking->post_event_id,
                'identification' => $booking->identification,
                'participants' => $booking->participants,
                'phone' => $booking->phone,
                'email' => $booking->email,
                'extra_message' => $booking->extra_message,
                'code' => $booking->code,
                'status' => 'forwarded'
            );
        
            $result = $wpdb->insert(
                $table_name,
                $data,
                array('%s','%d', '%d', '%s', '%d', '%s', '%s', '%s','%s','%s')
            );
        
            if ($result === false) {
                $error_message = $wpdb->last_error;
                $data = array('error'=>'1', 'error_message' =>"error inserting data into database");
                return $data;
            }
            
            $inserted_id = $wpdb->insert_id;
            $data = array('error'=>'0', 'data' => $booking, 'id' => $inserted_id);
            return $data;

        }else
        {
            $data = array('error'=>'1', 'error_message' =>"missing phone or identification in form");
            return $data;
        }


       
    }


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

    public function save_irish_pub_firenze_event($event) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ipf_events';
    
        print_r($event);
    
        // Save the event data to the database
        $result = $wpdb->insert(
            $table_name,
            array(
                'event_title' => $event->event_title,
                'date_event' =>  $event->date_event,
                'type_event' => $event->event_type,
                'maxnum' => $event->eventPlaces,
                'description_event' => $event->event_description,
                'image_path' =>  $event->image_url,
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result === false) {
            $error_message = $wpdb->last_error;
            echo "Error inserting data: $error_message";
            return false;
        } else {
            return true;
        }
       
    }


    public function tfIpf_query_booking_on_date($date)
    {
        global $wpdb;
        
        $query = $wpdb->prepare("
            SELECT COUNT(*) AS total_bookings
            FROM {$wpdb->prefix}ipf_bookings
            WHERE DATE(date_book) = %s
        ", $date);

        
        $totalBookings = $wpdb->get_var($query);

        if($totalBookings  == null)
        {
            $totalBookings  = 0;
        }
        

        return $totalBookings;
    } 

    public function tfIpf_query_bookings_on_date_noCount($date)
    {
        global $wpdb;
        
        $query = $wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}ipf_bookings
            WHERE DATE(date_book) = %s
        ", $date);

        $results = $wpdb->get_results($query);

        $booking_arr = array();

        if ($results) {
            foreach ($results as $single_booking) {
                
                $booking = new StdClass();
                $booking->status = $single_booking->status; 
                $booking->time = date("H:i", strtotime($single_booking->date_book));
                $booking->identification = $single_booking->identification;
                $booking->participants = $single_booking->participants;

                $booking_arr[] = $booking;
            }
        }
       

        return $booking_arr;
    } 

    // function irish_pub_firenze_query_events($startdate, $enddate)
    // { 
    //     global $wpdb;

    //     $start_date = date('Y-m-d', strtotime($startdate));
    //     $end_date = date('Y-m-d', strtotime($enddate));

    //     $events = [];

    //     $query = $wpdb->prepare("
    //         SELECT *
    //         FROM {$wpdb->prefix}ipf_events
    //         WHERE date_event BETWEEN %s AND %s
    //     ", $start_date, $end_date);

    //     $results = $wpdb->get_results($query);

    //     if ($results) {
    //         foreach ($results as $event) {
                
    //             $single_vent = array();

    //             $single_ev = new stdClass();
    //             $single_ev->event_id = $event->event_id;
    //             $single_ev->event_title = $event->event_title;

    //             // Assuming date_event is stored as a DATE type
    //             $single_ev->date_event = $event->date_event;
    //             $single_ev->time_event = ''; // No time component available for DATE type

    //             $single_ev->type_event = $event->type_event;
    //             $single_ev->maxnum = $event->maxnum;
    //             $single_ev->booked = $event->booked;
    //             $single_ev->description_event = $event->description_event;
    //             $single_ev->image_path = $event->image_path;

    //             $single_vent["event"] = $single_ev;
    //             $query_booking = $wpdb->prepare("
    //                 SELECT COUNT(*)
    //                 FROM {$wpdb->prefix}ipf_bookings
    //                 WHERE event_id = %d
    //             ", $event->event_id, $start_date, $end_date);

    //             $single_vent["bookings"] = $wpdb->get_var($query_booking);
    //             //get event booking
    //             // $query_booking = $wpdb->prepare("
    //             //     SELECT *
    //             //     FROM {$wpdb->prefix}ipf_bookings
    //             //     WHERE %d BETWEEN %s AND %s
    //             // ", $single_ev->event_id, $start_date, $end_date);

    //             // $all_event_bookings = $wpdb->get_results($query_booking);

    //             // if ($all_event_bookings) {
    //             //     foreach ($all_event_bookings as $single_booking) {
                        
    //             //         $booking = new StdClass();
    //             //         $booking->status = $single_booking->status; 
    //             //     }
    //             // }


    //             array_push($events, $single_vent);
    //         }
    //     }

    //     return $events;

    // }

    function get_booking_by_post_booking_id($post_booking_id) {
        
        global $wpdb;
    
        if (empty($post_booking_id)) {
            return false;
        }
    
        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}ipf_bookings WHERE post_booking_id = %d", $post_booking_id);
        $booking = $wpdb->get_row($query);
    
        if ($booking) {
            return $booking;
        } else {
            return false;
        }
    }

    public function tfIpf_create_dbtable() {

        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ipf_bookings';
        $sql = "SHOW TABLES LIKE '$table_name'";
    
        // Execute the query
        $table_exists = $wpdb->get_var($sql);
    
        if ($table_exists != $table_name) {
            
            $charset_collate = $wpdb->get_charset_collate();
    
            $sql_booking = "CREATE TABLE $table_name (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(6) COLLATE utf8mb4_general_ci NOT NULL,
                participants INT NOT NULL,
                identification VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
                phone VARCHAR(20) COLLATE utf8mb4_general_ci NOT NULL,
                email VARCHAR(255) COLLATE utf8mb4_general_ci,
                extra_message TEXT COLLATE utf8mb4_general_ci,
                post_event_id INT,
                status ENUM('forwarded', 'confirmed', 'cancelled') COLLATE utf8mb4_general_ci NOT NULL
            ) $charset_collate;";
                        
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql_booking); 
        } else {
            // Table already exists
            echo "Table already exists!";
        }

        $table_name = $wpdb->prefix . 'days_date';
        $sql = "SHOW TABLES LIKE '$table_name'";
    

        $table_exists = $wpdb->get_var($sql);

        if ($table_exists != $table_name) {
            
            $table_name = $wpdb->prefix . 'days_date';
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id BIGINT PRIMARY KEY NOT NULL,
                bookings INT NOT NULL,
                max_participants INT NOT NULL
            )";

            // Execute the query
            $wpdb->query($sql);
        } else {
            // Table already exists
            echo "Table already exists!";
        }
    }
    

}