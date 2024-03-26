<?php

include_once dirname( __FILE__ ) . 'TfIpfDatabase.php';
include_once dirname( __FILE__ ) . 'TfIpfManager.php';


class Tf_Ipf_Booking {

    private Tf_Ipf_Database $_ipfDatabase;
    private Tf_Ipf_Manager $_manager;


    function __construct(Tf_Ipf_Database $database, Tf_Ipf_Manager $manager)
    {
        $this->_ipfDatabase = $database;

        $this->_manager = $manager;
    }
    

    public function create_booking($iddate, $code, $participants, $identification, $phone,/*  $email, */ $extra_message, $status, $timebooking, $post_event_id = 0) {
        
        global $wpdb;

        $table_name = $wpdb->prefix . 'ipf_bookings';

        $data = array(
            'code' => $code,
            'participants' => $participants,
            'identification' => $identification,
            'phone' => $phone,
            // 'email' => $email,
            'extra_message' => $extra_message,
            'post_event_id' => $post_event_id,
            'status' => $status,
            'time_booking'=> $timebooking,
            'date_id' => $iddate
        );

        $success = $wpdb->insert($table_name, $data);

        if ($success !== false) {
            return $wpdb->insert_id;
        } else {
            return false;
        }
    }

    public function read_booking($id) {

        global $wpdb;

        $table_name = $wpdb->prefix . 'ipf_bookings';

        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
        $result = $wpdb->get_row($query);

        return $result;
    }

    public function update_booking($id, $data) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ipf_bookings';

        $wpdb->update($table_name, $data, array('id' => $id));
    }

    public function delete_booking($id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ipf_bookings';

        $wpdb->delete($table_name, array('id' => $id));
    }


    public function tfIpf_return_booking_date_form()
    {
        $html = "";

        if(isset($_POST['bookingdate']) && isset($_POST['bookingtime']) && !empty($_POST['bookingtime']) && !empty($_POST['bookingdate']))
        {

            $time = date('H:i', strtotime(sanitize_text_field( esc_attr($_POST['bookingtime']))));
            $sandate = sanitize_text_field( esc_attr($_POST['bookingdate']) );
            

            $date = strtotime($sandate);
            $format_date = date('Y-m-d', $date);

            $object_date = strtotime($format_date . " " . $time);

            if ($date == false) {
                
                $response = array(
                    'succeded' => 1,
                    'htmlToPrint' => $this->_ipfDatabase->PrintErrorMessage(" date is not valid")
                );
    
                $encoded_answer = json_encode($response);
                header('Content-Type: application/json');
    
                echo $encoded_answer;
                exit();
            }

            $html .= '<div class="row">
                        <div class="col-12 mini-riepilogo">
                            <div class="alert alert-success" role="alert">
                                <div class="data">
                                    <i class="fa-solid fa-calendar-days"></i> '. date('d-m-Y ', strtotime($format_date))  . ' <i class="fa-solid fa-clock"></i> '. $time . '</i>
                                </div>
                            </div>  
                        </div>
                        <a name="prenota"></a>
                        <div class="col-12">
                            <form id="regForm" action="#prenota-online">
                                <input style="display:none;" name="bookingdate" value="'.  $object_date .'">
                                <div class="tab form-row">
                                    <div class="form-group col-12">
                                        <label for="nomecompleto">Nome completo</label>
                                        <input type="text" id="uname" name="uname"  class="form-control" placeholder="Nome completo" >
                                    </div>
                                    <div class="form-group ">
                                        <label for="exampleFormControlSelect1">Persone</label>
                                        <input type="number" min="1" max="20" class="form-control"  id="uguest" name="uguest" placeholder="Numero di partecipanti">
                                    </div>
                                </div>
                                <div class="tab">
                                        <div class="form-group">
                                    <label for="numerotelefono">N. di Telefono</label><br>
                                    <input type="text" class="form-control" placeholder="Telefono"  id="uphone" name="uphone"  ><br>
                                    <small id="telefono" class="form-text text-muted">Riceverai un messaggio su questo numero.</small>
                                </div>
                                <div class="form-group">
                                    <label >Richieste particolari</label>
                                    <textarea id="uspecial" name="uspecial" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="condition" name="condition" onclick="SetValueCheckBox(this)" value="0" >
                                    <label class="form-check-label" for="condition">
                                    Accetto le <a>condizioni di utilizzo</a> e ho letto l\'<a>informativa privacy</a>.
                                    </label>
                                </div>

                                </div>
                            <div style="overflow:auto;">
                                <button type="button" id="prevBtn" onclick="nextPrev(-1)" class="btn btn-outline-primary indietro">Indietro</button>
                                <button type="button" id="nextBtn" onclick="nextPrev(1)" class="btn btn-success">Avanti</button>
                            </div>
                            <div style="text-align:center;margin-top:40px;">
                                <span class="step"></span>
                                <span class="step"></span>
                                <span class="step"></span>
                            </div>
                            </form>

                            <script>

                                var currentTab = 0;
                                showTab(currentTab);

                                var input = document.querySelector("#uphone");

                                var iti = window.intlTelInput(input, {

                                    allowDropdown: true,
                                    initialCountry: "it",
                                    autoPlaceholder: "polite",
                                    separateDialCode: true,
                                    utilsScript: "https://raw.githack.com/jackocnr/intl-tel-input/master/build/js/utils.js"
                            
                                });


                            </script>';

            $response = array(
                'succeded' => 1,
                'htmlToPrint' => $html
            );

            $encoded_answer = json_encode($response);
            header('Content-Type: application/json');

            echo $encoded_answer;
            exit();
        }

        $response = array(
            'succeded' => 0,
            'htmlToPrint' => $html
        );

        $encoded_answer = json_encode($response);
        header('Content-Type: application/json');

        echo $encoded_answer;
        exit();
    }






    public function tfIpf_create_booking_confirm_booking()
    {
        $datebooking = strtotime(date('Y-d-m'));
        $timebooking = time();
        $date_entity = new stdClass();


        if(isset( $_POST['data_form']['bookingdate']))
        {
            $or_datebooking = sanitize_text_field( esc_attr( $_POST['data_form']['bookingdate']) );
            
            $datebooking = strtotime(date('Y-m-d', $or_datebooking));
            $timebooking = date('H:i', $or_datebooking);
           
        }

        $event_id = sanitize_key($_POST['data_form']['eventid']);
        if (filter_var($event_id, FILTER_VALIDATE_INT) !== false) {

            $datebooking = date('Y-m-d', get_post_meta($event_id, '_tfIpf_event_date_time', true));
            $datebooking = strtotime($datebooking);
            $timebooking = date('H:i', get_post_meta($event_id, '_tfIpf_event_date_time', true));


        }else
        {
            $event_id = 0;
        }

        $date_entity = $this->_ipfDatabase->tfIpf_get_date($datebooking);

        
        if($date_entity == false)
        {
            $response = array(
                'succeded' => 0,
                'htmlToPrint' => $this->_ipfDatabase->PrintErrorMessage("there is an error in date format")
            );

            $encoded_answer = json_encode($response);
            header('Content-Type: application/json');
            echo $encoded_answer;

            exit();
        }

        $identification =  sanitize_text_field($_POST['data_form']['uname']);
        $code = $this->_ipfDatabase->tfIpf_generate_code($identification);
        $participants = sanitize_text_field($_POST['data_form']['uguest']);
        $phone = sanitize_text_field($_POST['data_form']['uphone']);
        //$email= sanitize_email($_POST['data_form']['uemail']);
        $extra_message = sanitize_text_field($_POST['data_form']['uspecial']);

        $status = "forwarded";
        

        if($date_entity->max_participants - $participants < 0)
        {
            $response = array(
                'succeded' => 0,
                'htmlToPrint' => $this->_ipfDatabase->PrintErrorMessage("you enter too high number of participants maximum number is 45")
            );

            $encoded_answer = json_encode($response);
            header('Content-Type: application/json');
            echo $encoded_answer;

            exit();
        }


        $resulted_booking = $this->create_booking($date_entity->id, $code, $participants, $identification, $phone,/*  $email, */ $extra_message, $status, $timebooking, $event_id);

        if($resulted_booking == false)
        {
            $response = array(
                'succeded' => 0,
                'htmlToPrint' => $this->_ipfDatabase->PrintErrorMessage("there is an error when creating booking.")
            );

        
            $encoded_answer = json_encode($response);
            header('Content-Type: application/json');
            echo $encoded_answer;

            exit();
        }
        

        $updated_bookings = $this->_ipfDatabase->tfIpf_update_days_date_bookings($datebooking, $participants);

        if($updated_bookings == false)
        {
            $response = array(
                'succeded' => 0,
                'htmlToPrint' => $this->_ipfDatabase->PrintErrorMessage("max number of bookings reached")
            );

            $this->delete_booking($resulted_booking);

            $encoded_answer = json_encode($response);
            header('Content-Type: application/json');
            echo $encoded_answer;

            exit();
        }

        //based on data_form->countrycode decide language of message

        $result = $this->_manager->tf_ipf_send_confirmation($phone, $code, $identification, $date_entity->id, $participants);

        if(!$result)
        {
            $response = array(
                'succeded' => 0,
                'htmlToPrint' => $this->_ipfDatabase->PrintErrorMessage("there is a whatsapp error")
            );

            $encoded_answer = json_encode($response);
            header('Content-Type: application/json');
            echo $encoded_answer;

            exit();

        }else
        {
            $html = "";
            $html .= '<a name="prenota"><div class="prenota-online">
                        <form id="confirm_booking_form" action="#prenota-online" method="POST">
                            <input id="idbooking" style="display:none;" name="idbooking" value="'. $resulted_booking .  '" />
                            <div class="form-group form-prenotazione">
                                <label for="code"><div class="alert alert-info info-whatsapp" role="alert">Inserisci il <b>codice di conferma</b> che arriverà entro 2 minuti <b>via Whatsapp</b> al numero di telefono che hai fornito: <i class="fa-solid fa-arrow-down rimbalza"></i></div></label>
                                <input type="text" class="form-control codice-whatsapp" id="code" name="code" placeholder="000000" maxlength="6" pattern="[A-Za-z0-9]{6}" title="Please enter a 6-character alphanumeric code" required>
                            </div>

                            <button type="submit" class="btn btn-success invia-prenotazione" formaction="#prenota-online">Conferma prenotazione</button>

                        </form></div>
                    <script>

                    jQuery(document).ready(function($) {
                        document.getElementById("confirm_booking_form").addEventListener("submit", function(event) {
                            event.preventDefault();
                            
                            const formData = new FormData(this);
                            const jsonData = {};
                            formData.forEach(function(value, key) {
                                jsonData[key] = value;
                            });
                            BookingConfirm(jsonData);
                        });

                        


                        function BookingConfirm(codeconfirm) {
                            
                            $.ajax({
                                url: ajaxurl,
                                method: \'POST\',
                                data: {
                                    action: \'tf_ipf_confirm_booking\',
                                    data_data: codeconfirm
                                },
                                success: function(response) {
                                    $("#regForm").html(response);
                                },
                                error: function(xhr, status, error) {
                                    console.error(error);
                                    alert(\'booking not confirmed\');
                                }
                            });
                        }

                    });// My custom stuff for deleting my custom post type here
                    </script>';

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

    public function tfIpf_final_booking_confirm()
    {
        $html = '';

        if(isset( $_POST['data_data']['idbooking']) && isset($_POST['data_data']['code']))
        {
            $idbooking =  sanitize_text_field($_POST['data_data']['idbooking']);
            $booking_code = mb_strtoupper(sanitize_text_field($_POST['data_data']['code']));

            $confirm = $this->_ipfDatabase->tfIpf_verify_code($idbooking, $booking_code);

            if($confirm['error'] == 0)
            {
                $html .= '<div class="prenotazione-confermata"><i class="fa-solid fa-thumbs-up roll-in"></i><p>Grazie per aver prenotato presso il nostro locale! Non vediamo l&#39;ora di darti il benvenuto e farti vivere una bella serata!</p> <p>Ti aspettiamo!</p> <p><small>The Florence Irish Pub Firenze</small></p> </div>';
            }else if($confirm['error'] == 2)
            {   
                $html .= '<a name="prenota"><div class="prenota-online">
                            <form id="confirm_booking_form" action="#prenota-online" method="POST">
                                <input id="idbooking" style="display:none;" name="idbooking" value="'. $idbooking .  '" />
                                <div class="form-group form-prenotazione">
                                    <label for="code"><div class="alert alert-info info-whatsapp" role="alert"> Codice non corretto. Prova a reinserire <b>codice di conferma</b> ricevuto <b>via Whatsapp</b> al numero di telefono fornito: <i class="fa-solid fa-arrow-down rimbalza"></i></div></label>
                                    <input type="text" class="form-control codice-whatsapp" id="code" name="code" placeholder="000000" maxlength="6" pattern="[A-Za-z0-9]{6}" title="Please enter a 6-character alphanumeric code" required>
                                </div>

                                <button type="submit" class="btn btn-success invia-prenotazione" formaction="#prenota-online">Conferma prenotazione</button>

                            </form></div>
                        <script>

                        jQuery(document).ready(function($) {
                            document.getElementById("confirm_booking_form").addEventListener("submit", function(event) {
                                event.preventDefault();
                                
                                const formData = new FormData(this);
                                const jsonData = {};
                                formData.forEach(function(value, key) {
                                    jsonData[key] = value;
                                });
                                BookingConfirm(jsonData);
                            });

                            


                            function BookingConfirm(codeconfirm) {
                                
                                $.ajax({
                                    url: ajaxurl,
                                    method: \'POST\',
                                    data: {
                                        action: \'tf_ipf_confirm_booking\',
                                        data_data: codeconfirm
                                    },
                                    success: function(response) {
                                        $("#regForm").html(response);
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(error);
                                        alert(\'booking not confirmed\');
                                    }
                                });
                            }

                        });
                        </script>';
                echo $html;
                exit();
            }
            else
            {

                $html .= '<h1> Error '. $confirm['error_message'] .' </h1>';
            }
        }

        echo $html;
        exit();
    }


  
}
