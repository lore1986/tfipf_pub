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


    /*
    * Always call this function to update internal date
    */
    function update_starting_date($in_date, $direct)
    {
        $date1 = strtr($in_date, '/', '-');

        $timestamp = strtotime($date1);
        $phpDateString = date('d-m-Y', $timestamp);

        $direction = sanitize_text_field($direct);
        $date = DateTime::createFromFormat('d-m-Y', $phpDateString);

        if ($direction == 1) {
            $date->modify('+1 month');
        } else if($direction == 0){
            $date->modify('-1 month');
        }

        $this->startingDate = $date->format('d-m-Y');

    }

    
    public function get_day_bookings()
    {
        $startingDate = 0;
        $html = "";

        if(isset($_POST["timestampdate"]))
        {
            $startingDate = date('Y-m-d', $_POST["timestampdate"]);
            $results = $this->_ipfDatabase->tfIpf_query_bookings_on_date_noCount($startingDate);

            $html .= '<div class="row">';

            $html .= '<div class="col-sm-3">';
            $html .= '<span class="font-weight-bold">Status</span>';
            $html .= '</div>';

            $html .= '<div class="col-sm-3">';
            $html .= '<span class="font-weight-bold">Time</span>';
            $html .= '</div>';

            $html .= '<div class="col-sm-3">';
            $html .= '<span class="font-weight-bold">Identification</span>';
            $html .= '</div>';

            $html .= '<div class="col-sm-3">';
            $html .= '<span class="font-weight-bold">Participants</span>';
            $html .= '</div>';

            $html .= '</div>'; // End of label row
            
            if (count($results) > 0) {
                foreach ($results as $booking) {
                    
                    $html .= '<div class="row">';
    
                    $html .= '<div class="col-sm-3">' . $booking->status . '</div>';
                    $html .= '<div class="col-sm-3">' . $booking->time . '</div>';
                    $html .= '<div class="col-sm-3">' . $booking->identification . '</div>';
                    $html .= '<div class="col-sm-3">' . $booking->participants . '</div>';
                    
                    $html .= '</div>'; 
                }
                
            } else {
                $html .= "no booking for this day found";
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

        $calendar_events = $this->_ipfDatabase->tfIpf_event_query_list();
        $html = $this->tfIpf_render_events_list($calendar_events);
        echo $html;
        exit();
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


}
