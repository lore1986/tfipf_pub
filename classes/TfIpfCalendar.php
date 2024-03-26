<?php

include_once plugin_dir_path( __FILE__ ) . 'TfIpfDatabase.php';


class Tf_Ipf_Calendar {

    private $startingDate;
    private Tf_Ipf_Database $_ipfDatabase;
    private $daysArr = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    private $months = [
        0 => 'December',
        1 => 'January',
        2 => 'February',
        3 => 'Mach',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
      ];


    public function returnMonthsArray()
    {
        return $this->daysArr;
    }



    function __construct(Tf_Ipf_Database $database, $start_date = null){

        //add_action("wp_ajax_my_user_vote", "my_user_vote");

        if ($start_date === null) {
            $this->startingDate = date('d-m-Y');
        } else {
            $this->startingDate = $start_date;
        }

        $this->_ipfDatabase = $database;
    }



    public function get_day_bookings()
    {
        $startingDate = 0;
        $html = "";

        if(isset($_POST["timestampdate"]))
        {
            $startingDate = date('Y-m-d', $_POST["timestampdate"]);


            $results = $this->_ipfDatabase->tfIpf_query_bookings_on_date_noCount(strtotime($startingDate));

            $html .= '<div class="row">';

            $html .= '<div class="col-sm-1">';
            $html .= '<span class="font-weight-bold">Stato</span>';
            $html .= '</div>';
            
            $html .= '<div class="col-sm-3">';
            $html .= '<span class="font-weight-bold">Identification</span>';
            $html .= '</div>';

            $html .= '<div class="col-sm-2">';
            $html .= '<span class="font-weight-bold">Phone Number</span>';
            $html .= '</div>';


            $html .= '<div class="col-sm-1">';
            $html .= '<span class="font-weight-bold">Time</span>';
            $html .= '</div>';

            $html .= '<div class="col-sm-1">';
            $html .= '<span class="font-weight-bold">Numero</span>';
            $html .= '</div>';

            $html .= '<div class="col-sm-2">';
            $html .= '<span class="font-weight-bold">Modifica</span>';
            $html .= '</div>';

            $html .= '</div>';
            
            if (count($results) > 0) {
                foreach ($results as $booking) {
                    $html .= '<div class="row mt-2">';
            
                    $html .= '<div class="col-sm-1">' . ($booking->status == 'confirmed' ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-warning"></i>') . '</div>';
                    $html .= '<div class="col-sm-3">' . $booking->identification . '</div>';
                    $html .= '<div class="col-sm-2">' . $booking->phone . '</div>';
                    $html .= '<div class="col-sm-1">' . date("H:i", strtotime($booking->time_booking)) . '</div>';
                    
                    $html .= '<div class="col-sm-1">' . $booking->participants . '</div>';
            
                    $html .= '<div class="col-sm-2"><button onclick="RetrieveBooking(this)" class="btn btn-primary edit-booking" data-booking-id="' . $booking->id . '">Edit</button></div>';
            
                    $html .= '</div>'; 
                }
            } else {
                $html .= "No bookings for this day found";
            }
            
            
            echo $html;
            exit();
        }else
        {
            echo $html;
            exit();
        }
    }

    public function get_admin_calendar()
    {
        $startingDate = date('d-m-Y');


        if(isset($_POST["datestart"]))
        {
            $startingDate = date('d-m-Y', $_POST["datestart"]);
        }

        if(isset($_POST["direction"]))
        {
            $direction =$_POST["direction"];

            if($direction == 1)
            {
                $startingDate = date('Y-m-01', strtotime('+1 month', strtotime($startingDate)));

            }else if($direction == 0)
            {
                $startingDate = date('Y-m-01', strtotime('-1 month', strtotime($startingDate)));
            }
            
        }
        
        $daysArr = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

        
    
        // Start generating the HTML
        $html = '<div class="container">';

        // Determine the first day of the month
        $firstDayOfMonth = date('N', strtotime(date('01-m-Y', strtotime($startingDate))));
    
        // Calculate the month and year of the previous month
        $month = date('m', strtotime('-1 month', strtotime($startingDate)));
        $year = date('Y', strtotime('-1 month', strtotime($startingDate)));
    
        // Get the total number of days in the previous month
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    
        // Calculate the starting day of the range
        $startingDay = $totalDaysInMonth - $firstDayOfMonth + 1;
    
        // Initialize an array to store the last X days of the previous month
        $lastXDays = array();
    
        // Iterate from the starting day to the last day of the previous month
        for ($day = $startingDay; $day <= $totalDaysInMonth; $day++) {
            $lastXDays[] = $day;
        }
    
        // Generate the calendar grid
        $currentDay = 1;
        $currentExtra = 1;
        $daysInMonth = date('t', strtotime($startingDate));
        for ($i = 1; $i <= 5; $i++) { // Assume maximum of 6 rows
            $html .= '<div class="row">';
            for ($j = 1; $j <= 7; $j++) {
                
                $dateToPrint = "";
                $dateToSearch = "";

                if ($i == 1 && $j < $firstDayOfMonth) {
                    // Add days from the previous month
                    if (!empty($lastXDays)) {
                        $previousMonthDate = array_shift($lastXDays) + 1;
                        $dateToPrint = date('d-m', strtotime("$previousMonthDate-$month-$year"));
                        $dateToSearch = date('Y-m-d', strtotime("$previousMonthDate-$month-$year"));
                    }

                } else {
                    // Add days from the current month
                    if ($currentDay <= $daysInMonth) {
                        
                        $dateToPrint =  date('d-m', strtotime(date('Y-m-', strtotime($startingDate)) . $currentDay));
                        $dateToSearch = date('Y-m-d', strtotime(date('Y-m-', strtotime($startingDate)) . $currentDay));

                        $currentDay++;

                    } else {
                        $date_next_month = date('m', strtotime('+1 month', strtotime($startingDate)));
                        $dateToPrint =  date('d-m', mktime(0, 0, 0, $date_next_month, $currentExtra));
                        $dateToSearch = date('Y-m-d', strtotime($dateToPrint));       
                        $currentExtra++;
                    }
                }

                $html .= '<div class="col border clickable-day"  data-date="'. strtotime($dateToSearch)  .'">';                               
                $html .= '<p class="float-right my-auto ">' . $dateToPrint . '</p>';

                $totalBookings = $this->_ipfDatabase->tfIpf_query_booking_on_date($dateToSearch);
                $badgeColorClass = $totalBookings > 0 ? 'badge-success' : 'badge-primary';
                $html .= '<span class="badge badge-pill ' . $badgeColorClass . '">' . $totalBookings . '</span>';
                $html .= '</div>';
            }
            $html .= '</div>'; // Close row
        }
    
        $html .= '</div>'; // Close container
    
        // Return the generated HTML

        $response = array(
            'newTimestamp' => strtotime($startingDate),
            'newDate' => date('d-m-Y', strtotime($startingDate)),
            'htmlToPrint' => $html
        );

        $encoded_answer = json_encode($response);
        header('Content-Type: application/json');

        echo $encoded_answer;
        exit();
       
    }


    /*
    * Registered action to output full calendar
    */
    public function get_calendar_html() {
        
        if(isset($_POST['maxnum']))
        {
            $maxnum = sanitize_text_field($_POST['maxnum']);
            $calendar_events = $this->_ipfDatabase->tfIpf_event_query_list(intval($maxnum));
            $html = $this->tfIpf_render_events_list($calendar_events);
            echo $html;
            exit();
        }
        
    }

    

    function tfIpf_render_events_list($arra_event)
    {
        $html = '';

        foreach($arra_event as $ev)
        {
            $ev_date = date('Y-m-d', strtotime($ev->date_event));
            $ev_link = $permalink = get_permalink($ev->id);

            $featured_image_url = get_the_post_thumbnail_url($ev->id, 'thumbnail');


            switch ($ev->event_type) {
                case 'sport':
                    {
                        $path = plugin_dir_url( __DIR__) . 'serie-a/';

                        $team_one_img = $path . $ev->teamone . '.png';
                        $team_two_img = $path . $ev->teamtwo . '.png';

                        if(empty($team_one_img) || $team_one_img == null)
                        {
                            $team_one_img = $path . 'default_team.jpg';
                        }

                        if(empty($team_two_img) || $team_two_img == null)
                        {
                            $team_two_img = $path . 'default_team.jpg';
                        }



                        $html .= '<div class="eventi-home-riga evento-sport">
                                            <img src="' . $team_one_img . '" alt="flag squadra1">
                                            <img src="' . $team_two_img . '" alt="flag squadra2">
                                            <a href="'. $ev_link .'">
                                                <span class="squadra1">'. $ev->teamone .'</span>
                                                - <span class="squadra2">'. $ev->teamtwo .'</span>
                                            </a>';
                    }
                    break;
                case 'food':
                    {
                        $html .= '<div class="eventi-home-riga evento-degustazione">
                                    <img src="'.$featured_image_url.'" alt="Titolo Evento">
                                    <a href="'. $ev_link .'">'. $ev->title .'</a>';
                    }
                    break;
                case 'music':
                    {
                        $html .= '<div class="eventi-home-riga evento-music">
                                        <img src="'. $featured_image_url.'" alt="Titolo Evento">
                                        <a href="'. $ev_link .'">'. $ev->title .'</a>';
                    }
                    break;
                default:

                    break;
            }

            $html .= '<p>
                                <span class="data-evento">
                                <b>'. date('d M Y', strtotime($ev_date)) .'</b>
                                </span>, <span class="orario-evento">'. $ev->time_event .' </span>

                                <button type="button" onclick="location.href=\'' . $ev_link . '\'" class="btn btn-outline prenota">Prenota</button>
                            </p>
                    </div>';

        }

        return $html;
    }




    /*
    * Get previous month on array
    */
    function get_previous_month($ddate) {

        $current_month_number = (int) date('m', strtotime($ddate));
        return $months[$current_month_number - 1];
    }

    public function tfipf_return_edit_booking_form_ajax()
    {
        $html = '';

        if(isset($_POST["bookingid"]))
        {
            $bookingid = sanitize_text_field( $_POST['bookingid'] );

            $booking = $this->tfIpf_return_booking_by_id(intval($bookingid));


            $html = '
                <form id="editBookingForm">
                    <input type="hidden" value="'. $booking->id. '" id="bookingid" name="bookingid">
                    <div class="form-group">
                        <label for="time_booking">Orario:</label>
                        <input type="time" class="form-control" value="'. date("H:i", strtotime($booking->time_booking)) . '" id="time_booking" name="time_booking">
                    </div>
                    <div class="form-group">
                        <label for="identification">Nome prenotazione:</label>
                        <input type="text" class="form-control" value="'. $booking->identification . '" id="identification" name="identification">
                    </div>
                    <div class="form-group">
                        <label for="status">Stato:</label>
                        <input type="text" class="form-control" value="'. $booking->status . '" id="status" name="status">
                    </div>
                    <div class="form-group">
                        <label for="participants">Numero partecipanti:</label>
                        <input type="number" class="form-control" value="'. $booking->participants . '" id="participants" name="participants">
                    </div>
                    <div class="form-group">
                        <label for="phone">Numero di telefono:</label>
                        <input type="text" class="form-control" value="'.  $booking->phone . '" id="phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="date_id">Data prenotazione:</label>
                        <input type="date" class="form-control" value="'. date('Y-m-d', $booking->date_id) . '" id="date_id" name="date_id">
                    </div>
                    <div class="form-group">
                        <label for="code">Code:</label>
                        <input class="form-control" value="'. $booking->code . '" id="code" name="code" disabled>
                    </div>

                    <div class="form-group">
                        <label for="post_event_id">Post Event id:</label>
                        <input type="number" class="form-control" value="'. $booking->post_event_id . '" id="post_event_id" name="post_event_id">
                    </div>
                    <div class="form-group">
                        <label for="extra_message">Extra Message:</label>
                        <textarea class="form-control"  id="extra_message" name="extra_message">'. $booking->extra_message . '</textarea>
                    </div>
                    
                    <button type="button" onclick="save_edit_form_data()" class="btn btn-primary">Salva le Modifiche</button>
                </form>
            ';


            $response = array(
                'succeded' => 1,
                'htmlToPrint' => $html
            );

            $encoded_answer = json_encode($response);
            header('Content-Type: application/json');

            echo $encoded_answer;
            exit();
        }
        

        
    } 


    public function tfIpf_return_booking_by_id($bookingid)
    {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}ipf_bookings
            WHERE id = %d
        ", $bookingid);

        $booking = $wpdb->get_row($query);

        if ($booking) {
            
            return $booking;

        } else {
            
            return false;
        }
    }

    public function ifpsave_edit_booking()
    {
        if (isset($_POST['formData'])) {
            $formData = json_decode(stripslashes($_POST['formData']), true);
    
            $booking = new stdClass();
            $booking->id = intval($formData['bookingid']);
            $booking->post_event_id = intval($formData['post_event_id']);
            $booking->identification = $formData['identification'];
            $booking->participants = intval($formData['participants']);
            $booking->phone = $formData['phone'];
            $booking->extra_message= $formData['extra_message'];

            //check if update this
            $booking->code = $formData['code'];
            
            $booking->status  = $formData['status'];
            $booking->time_booking = date("H:i", strtotime($formData['time_booking']));
            $booking->date_id = strtotime($formData['date_id']);

        
            global $wpdb;

            $table_name = $wpdb->prefix . 'ipf_bookings';

            $$result = $wpdb->update(
                $table_name,
                (array) $booking,
                array('id' => $booking->id),
                array(
                    '%d', 
                    '%d', 
                    '%s', 
                    '%d', 
                    '%s', 
                    '%s', 
                    '%s', 
                    '%s', 
                    '%s', 
                    '%d'  
                ),
            );


            if ($result === false) {

                echo "error";

            } else {
                $response = array(
                    'success' => true,
                    'htmlToPrint' => 'Booking data saved successfully'
                );
        
                wp_send_json($response); // Send JSON response back to the client
            }
            

           
        }
    
        exit();
    }



}
