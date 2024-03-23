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

        add_action( 'init', [$this ,'tfIpf_register_booking_post_type'] );

        add_action('manage_tfipfbooking_posts_custom_column', array($this,'tfipfbooking_tfipfbookingcode_column'), 10, 2);

        add_action( 'save_post_tfipfbooking', [$this, 'tfIpf_ipfbooking_post_admin'], 10, 3);

        add_action('pre_get_posts', [$this, 'tfIpf_reorder_list_bookings_admin']);

        add_filter ( 'manage_posts_columns', [$this, 'tfipf_reorder_columns' ], 10, 2);
        
        
    }




    function tfipf_reorder_columns( $columns, $post_type ) {

        if($post_type === 'tfipfbooking')
        {
            unset($columns['date']);
            
            return array_merge( $columns, 
                array( 
                    'tfipfbookingidentification' => __( 'Identificativo Prenotazione', 'tfIpfpub' ),
                    'tfipfbookingcode' => __( 'Codice Prenotazione', 'tfIpfpub' ),
                    'tfipfbookingdate' => __( 'Data Prenotazione', 'tfIpfpub' ),
                    'tfipfbookingtime' => __( 'Orario Prenotazione', 'tfIpfpub' ),
                    'tfipfbookingphone' => __( 'Contatto Telefonico', 'tfIpfpub' ),
                ));
        }else {

            return $columns;
        }
        
    }
    

    // function wpa_post_link( $url, $post ){
    //     if ( $meta = get_post_meta( $post->ID, 'query_arg', true ) ) {
    //         $url = add_query_arg( 'param', $meta, $url );
    //     }
    //     return $url;
    // }
    // add_filter( 'post_link', 'wpa_post_link', 10, 2 );
    
    
    
    
    

    
    function tfIpf_reorder_list_bookings_admin( $q ) {
        
        if ( !is_admin() || !$q->is_main_query()) { // || 
            return;
        }
    
        $s = get_current_screen();
    
        if($s->post_type !== 'tfipfbooking' ) {
            return $q;
        }
    
        global $wpdb;
        $today_date = date('Y-m-d');
        $table_name = $wpdb->prefix . 'ipf_bookings';
    
        $q->set('orderby', "CASE WHEN DATE(date_book) >= '$today_date' THEN 0 ELSE 1 END, date_book");
        $q->set('order', 'ASC');
  
    }




    function tfipfbooking_tfipfbookingcode_column( $column, $post_id ) {

        $post_type = get_post_type($post_id);

        if ($post_type === 'tfipfbooking') {
            $booking = $this->_ipfDatabase->get_booking_by_post_booking_id($post_id);
    
            switch ( $column ) {
    
                case 'tfipfbookingcode' :
                    if($booking != false) {
                        echo $booking->code;
                    }
                    break;
                case 'tfipfbookingdate':
                    if($booking != false) {
                        echo date('d-m-Y', strtotime($booking->date_book));
                    }
                    break;
                case 'tfipfbookingtime':
                    if($booking != false) {
                        echo date('H:i', strtotime($booking->date_book));
                    }
                    break;
                case 'tfipfbookingidentification':
                    if($booking != false) {
                        echo $booking->identification;
                    }
                    break;
                case 'tfipfbookingphone':
                    if($booking != false) {
                        echo $booking->phone;
                    }
                    break;
            }
        } else {
            return $column;
        }
    }

    public function tfIpf_return_booking_date_form()
    {
        $html = "";

        if(isset($_POST['bookingdate']) && isset($_POST['bookingtime']) && !empty($_POST['bookingtime']) && !empty($_POST['bookingdate']))
        {

            $time = date('H:i', strtotime(sanitize_text_field( esc_attr($_POST['bookingtime']))));
            $date = date('Y-m-d', strtotime(sanitize_text_field( esc_attr($_POST['bookingdate']) )));


            $html .= '<div class="row">
                        <div class="col-12 mini-riepilogo">
                             <div class="alert alert-success" role="alert"><div class="data"><i class="fa-solid fa-calendar-days"></i> '. date('d-m-Y ', strtotime($date))  .
                              ' </div><i class="fa-solid fa-clock"></i> '. $time . '

                        </div>  </div>
                        <a name="prenota"></a>
                        <div class="col-12">
                            <form id="regForm" action="#prenota-online">
                                <input style="display:none;" name="bookingdate" value="'.  strtotime($date . " " . $time) .'">
                                <div class="tab form-row">
                                    <input name="bookingid" style="display:none;" value="<?php echo $post_id ?>" />
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
                                    <label for="indirizzoemail">E-mail</label>
                                    <input type="email" id="umail" name="umail"  class="form-control" placeholder="E-mail">
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
        $booking_id =-1;
        $datebooking = date('Y-d-m');

        if(isset( $_POST['data_form']['bookingdate']))
        {
            $datebooking = sanitize_text_field( esc_attr( $_POST['data_form']['bookingdate']) );
        }

        $t_id = $_POST['data_form']['bookingid'];
        if (filter_var($t_id, FILTER_VALIDATE_INT) !== false) {

            $booking_id = sanitize_key($_POST['data_form']['bookingid']);
            $datebooking = get_post_meta($booking_id, '_tfIpf_event_date_time', true);
        }

        $booking = new stdClass();
        $booking->post_event_id = $booking_id;
        $booking->date = $datebooking;
        $booking->identification = sanitize_text_field($_POST['data_form']['uname']);
        $booking->participants = sanitize_text_field($_POST['data_form']['uguest']);
        
        $booking->phone = sanitize_text_field($_POST['data_form']['uphone']);
        $booking->email = sanitize_email($_POST['data_form']['umail']);
        $booking->extra_message = sanitize_text_field($_POST['data_form']['uspecial']);


        $booking_err = $this->_ipfDatabase->save_tfIpf_booking($booking);

        //based on data_form->countrycode decide language of message

        if($booking_err['error'] == 0)
        {

            $result = $this->_manager->tf_ipf_send_confirmation($booking_err['data']);

            if(!$result)
            {
                $response = array(
                    'succeded' => 0,
                    'htmlToPrint' => 'whatsapp error'
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
                                <input id="idbooking" style="display:none;" name="idbooking" value="'. $booking_err['id'] .  '" />
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

        }else
        {

        }


    }

    public function tfIpf_final_booking_confirm()
    {
        $html = '';

        if(isset( $_POST['data_data']['idbooking']) && isset($_POST['data_data']['code']))
        {
            $idbooking =  sanitize_text_field($_POST['data_data']['idbooking']);
            $booking_code = sanitize_text_field($_POST['data_data']['code']);

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


    function tfIpf_register_booking_post_type() {

        $supports = array(
            'title', // post title
            'post-formats', // post formats
        );

        $labels = array(
            'name' => _x('Prenotazioni', 'plural'),
            'singular_name' => _x('Prenotazione', 'singular'),
            'menu_name' => _x('The Florence Prenotazioni', 'admin menu'),
            'name_admin_bar' => _x('The Florence Prenotazioni', 'admin bar'),
            'add_new' => _x('Aggiungi Prenotazione', 'add new'),
            'add_new_item' => __('Aggiungi Prenotazione'),
            'new_item' => __('Nuovo prenotazione'),
            'edit_item' => __('Modifica prenotazione'),
            'view_item' => __('Vedi prenotazione'),
            'all_items' => __('Tutti le prenotazioni'),
            'search_items' => __('Cerca prenotazione'),
            'not_found' => __('Nessuna prenotazione trovata.'),

            );


        $args = array(
            'supports' => $supports,
            'labels' => $labels,
            'public' => true,
            'query_var' => true,
            'publicly_queryable'  => false,
            'rewrite' => array('slug' => 'tfipfbooking'),
            'has_archive' => true,
            'hierarchical' => false,
            'register_meta_box_cb' => [$this,  'tfIpf_booking_meta_box'],
            // 'capabilities' => array(
            //     'publish_posts' => 'ADD_CAP_HERE',
            //     'edit_posts' => 'ADD_CAP_HERE',
            //     'edit_others_posts' => 'ADD_CAP_HERE',
            //     'delete_posts' => 'ADD_CAP_HERE',
            //     'delete_others_posts' => 'ADD_CAP_HERE',
            //     'read_private_posts' => 'ADD_CAP_HERE',
            //     'edit_post' => 'ADD_CAP_HERE',
            //     'delete_post' => 'ADD_CAP_HERE',
            //     'read_post' => 'ADD_CAP_HERE',
            // ),
        );


        register_post_type( 'tfipfbooking' , $args );
    }



    function tfIpf_booking_meta_callback($post){


        wp_nonce_field( 'tf_ipf_nonce_global', 'tfIpf_one_once' );

        $book_date = date('Y m d');
        $time_book = date('H:i');

        $booking = $this->_ipfDatabase->get_booking_by_post_booking_id($post->ID);

        if($booking->post_event_id == null)
        {
            $book_date = date('Y-m-d');
            $time_book = date('H:i');

        }else if($booking->post_event_id != -1)
        {
            $book_date = date('Y-m-d', get_post_meta( $booking->post_event_id, '_tfIpf_event_date_time', true));
        }else
        {
            $book_date = date('Y-m-d', strtotime($booking->date_book));
            $time_book = date('H:i', strtotime($booking->date_book));
        }

        $events = $this->_ipfDatabase->tfIpf_event_query_list();

        ?>

        <input type="text" id="post_event_id" style="display:none;" name="post_event_id" value="<?php echo ($booking) ? $booking->post_event_id : ''; ?>">

        <div style="display: flex; margin-bottom: 10px;">
            <div class="form-group" style="flex: 1;">
                <label for="date_event"><?php _e('Data Prenotazione (formato data: MESE-GIORNO-ANNO)', 'textdomain'); ?></label><br>
                <input type="date" style="width: 45%;" id="event_date" name="event_date" value="<?php echo $book_date; ?>">
                <input type="time" style="width: 45%;" id="event_time" name="event_time" value="<?php echo $time_book; ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="event_select">Select Event:</label><br>
                <select style="width: 100%;" class="form-control chosen-select" id="event_select" name="event_select" data-placeholder="Search for an event...">
                    <?php
                    if ($booking->post_event_id == -1 || $booking->post_event_id == null ) {
                        echo '<option value="-1"> Tavolo (NO EVENTO) </span></option>';
                    }else
                    {
                        echo '<option value="' . $booking->post_event_id . '">' . get_the_title($booking->post_event_id) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Data Evento: ' . date('d/m/Y', strtotime($book_date)) . '</span></option>';

                    }
                    ?>
                </select>
            </div>
        </div>

        <div style="display: flex; margin-bottom: 10px;">
            <div style="width: 50%; margin-right: 10px;">
                <div class="form-group">
                    <label for="identification">Identification:</label>
                    <input type="text" style="width: 100%;" id="identification" name="identification" value="<?php echo $booking->identification ?>">
                </div>
                <div class="form-group">
                    <label for="participants">Participants:</label>
                    <input type="text" style="width: 100%;" id="participants" name="participants" value="<?php echo $booking->participants ?>">
                </div>
            </div>
            <div style="width: 50%;">
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" style="width: 100%;" id="phone" name="phone" value="<?php echo $booking->phone ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" style="width: 100%;" id="email" name="email"  value="<?php echo $booking->email ?>">
                </div>
            </div>
        </div>

        <div style="display: flex; margin-bottom: 10px;">
            <div style="width: 50%; margin-right: 10px;">
                <div class="form-group">
                    <label for="code">Code:</label>
                    <input type="text" style="width: 100%;" id="code" name="code" value="<?php echo $booking->code ?>"  readonly>
                </div>
            </div>
            <div style="width: 50%;">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select style="width: 100%;" id="status" name="status" readonly>
                        <option value="forwarded" <?php if($booking->status === 'forwarded') echo 'selected'; ?>>Forwarded</option>
                        <option value="confirmed" <?php if($booking->status === 'confirmed') echo 'selected'; ?>>Confirmed</option>
                        <option value="cancelled" <?php if($booking->status === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="extra_message">Extra Message:</label>
            <textarea style="width: 100%;" id="extra_message" name="extra_message"><?php echo $booking->extra_message ?></textarea>
        </div>



        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
        <script>
            jQuery(document).ready(function($) {
                $('.chosen-select').chosen({ no_results_text: "Oops, nothing found!" });

                // Event listener for Chosen select change
                $('#event_select').on('change', function() {
                    var selectedEventId = $(this).val();
                    $('#post_event_id').val(selectedEventId);
                });

                $('#event_select_chosen').on('keyup', '.chosen-search input', function() {

                    var searchText = $(this).val();
                    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

                    if(searchText.length >= 2)
                    {

                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'tf_ipf_filter_events',
                                usersearch: searchText
                            },
                            success: function(response) {
                                // $('#event_select').trigger('chosen:open');
                                // $('.chosen-search-input').val(searchText)
                                $('#event_select').html(response).trigger('chosen:updated');
                            }
                        });
                    }
                });
            });
        </script>
        <?php
    }

    function tfIpf_booking_meta_box()
    {
        $screens = array( 'tfipfbooking' );

        foreach ( $screens as $screen ) {
            add_meta_box(
                'tf_ipf_booking_metabox',
                __( 'Prenotazione', 'sitepoint' ),
                [$this, 'tfIpf_booking_meta_callback'],
                $screen
            );
        }
    }


    function tfIpf_ipfbooking_post_admin($post_id, $post, $update )
    {
        if($update)
        {
            if($post->post_type == "tfipfbooking")
            {
                $saved_value = get_post($post_id );


                $post->post_title = html_entity_decode($post->post_title);

                $date = date('Y m d');
                $book_date = date('Y m d');
                $time_book = date('H:i');

                $post_event_id = -1;

                if(!empty($_REQUEST['post_event_id']))
                {
                    $post_event_id = $_REQUEST['post_event_id'];
                }

                if($post_event_id != -1)
                {
                    $book_date = date('Y-m-d', strtotime($booking->date_book));
                    $time_book = date('H:i', strtotime($booking->date_book));

                    $date = $book_date . ' ' . $time_book;

                }else
                {
                    $book_date = date('Y-m-d', strtotime($_REQUEST['event_date']));
                    $time_book = date('H:i', strtotime($_REQUEST['event_time']));

                    $date = $book_date . ' ' . $time_book;
                }



                $booking = new stdClass();

                $booking->date              = $date;
                $booking->post_id           = $post_id;
                $booking->post_event_id     = $post_event_id;
                $booking->identification    = $_REQUEST['identification'];
                $booking->participants      = $_REQUEST['participants'];
                $booking->phone             = $_REQUEST['phone'];
                $booking->email             = $_REQUEST['email'];
                $booking->extra_message     = $_REQUEST['extra_message'];
                $booking->code              = $this->_ipfDatabase->tfIpf_generate_code($booking);
                $booking->status            = $_REQUEST['status'];
                $ret = $this->_ipfDatabase->tfipf_check_exist_and_execute($booking);

            }
        }


        return $post;

    }

    
    public function tfIpf_booking_delete( $postid, $post) {

        if ( 'tfipfbooking' !== $post->post_type ) {
            return;
        }

        //grab the booking in the database with the data related to the deleted booking post
        $deleted = $this->_ipfDatabase->tfIpf_delete_booking($postid);

        return;
    }


}
