<?php

    /* Template Name: Calendario Eventi */
    //;
    wp_head();


    ?>

<?php get_header();
	$post_id = get_the_ID();

    global $wp_query;

    if( isset( $wp_query->query_vars['available'] )) {
        echo 'custom variable recognized';
    }

	// Get all post meta data for the current post
	$post_meta = get_post_meta($post_id);
    $max_participants = isset( $_GET['available'] ) ? intval( $_GET['available'] ) : 0;
?>


<div id="primary">
    <div id="content" class="site-content <?php echo $post_meta['_tfIpf_event_type'][0] ?>" role="main">
        <div class="dett-ev-cont container">
            <div class="dett-ev">
                <div class="dett-music row">
                    <div class="col-12 col-md-6 immagine-evento">
                        <?php
                            $featured_image_url = get_the_post_thumbnail_url($post_id, 'full');
                            if ($featured_image_url) {
                                echo '<img src="' . $featured_image_url . '" alt="Featured Image">';
                            }
                        ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <h1><?php echo get_the_title($post_id) ?></h1>
                        <h2><?php echo date('d M Y', $post_meta['_tfIpf_event_date_time'][0]) ?></h2>
                        <h4><?php echo date('H:i', $post_meta['_tfIpf_event_date_time'][0]) ?></h4><hr>
                        <p class="descrizione-music"><?php echo $post_meta['_tfIpf_event_description'][0] ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4 squadre">
                        <img src="/wp-content/plugins/tfIp_Pub/serie-a/<?php echo  $post_meta['_tfIpf_event_team_one'][0] ?>.png" alt="<?php echo  $post_meta['_tfIpf_event_team_one'][0] ?>"> 
                        <img src="/wp-content/plugins/tfIp_Pub/serie-a/<?php echo $post_meta['_tfIpf_event_team_two'][0] ?>.png" alt="<?php echo $post_meta['_tfIpf_event_team_two'][0] ?>">
                    </div>
                    <div class="col-12 col-md-8 squadre">
                        <h1><?php echo  $post_meta['_tfIpf_event_team_one'][0] ?> - <?php echo $post_meta['_tfIpf_event_team_two'][0] ?></h1>
                    </div>
                    <div class="descrizione-sport">
                        <p>Presso il nostro locale troverai due maxischermi per seguire in diretta la partita <b><?php echo  $post_meta['_tfIpf_event_team_one'][0] ?> <?php echo $post_meta['_tfIpf_event_team_two'][0] ?></b> che verrà proiettata il giorno <?php echo date('d M Y', $post_meta['_tfIpf_event_date_time'][0]) ?>, a partire dalle ore <?php echo date('H:i', $post_meta['_tfIpf_event_date_time'][0]) ?> al <b>The Florence Irish Pub</b> a Firenze.</p>
                    </div>
                    <div>
                        <a name="prenota"></a>
                        <h3>Prenota un tavolo:</h3>
                        <form id="regForm" class="form-prenota-evento" action="#prenota-online" method="POST" >
                            <div class="tab form-row">
                                <input name="bookingid" style="display:none;" value="<?php echo $post_id ?>" />
                                <div class="form-group col-12">
                                    <label for="nomecompleto">Nome completo</label>
                                    <input type="text" id="uname" name="uname"  class="form-control" placeholder="Nome completo" >
                                </div>
                                <div class="form-group ">
                                    <label for="exampleFormControlSelect1">Persone</label>
                                    <input type="number" min="1" max="<?php echo $max_participants ?>" data-max="<?php echo $max_participants ?>" class="form-control"  id="uguest" name="uguest" placeholder="Numero di partecipanti">
                                </div>
                            </div>
                            <div class="tab">
                                <div class="form-group">
                                    <label for="numerotelefono">N. di Telefono</label><br>
                                    <input type="text" class="form-control" placeholder="Telefono"  id="uphone" name="uphone"  ><br>
                                    <small id="telefono" class="form-text text-muted">Riceverai un messaggio di conferma per la tua prenotazione.</small>
                                </div>
                                <div class="form-group">
                                    <label for="indirizzoemail">E-mail</label>
                                    <input type="email" id="uemail" name="uemail"  class="form-control" placeholder="E-mail">
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="condition" name="condition" onclick="SetValueCheckBox(this)" value="0" >
                                    <label class="form-check-label" for="condition">
                                    Accetto le <a>condizioni di utilizzo</a> e ho letto l'<a>informativa privacy</a>.
                                    </label>
                                </div>
                            </div>
                            <div style="overflow:auto;">
                                <button type="button" id="prevBtn" onclick="nextPrev(-1)" class="btn btn-outline-primary">Indietro</button>
                                <button type="button" id="nextBtn" onclick="nextPrev(1)" class="btn btn-success">Avanti</button>
                            </div>
                            <div style="text-align:center;margin-top:40px;">
                                <span class="step"></span>
                                <span class="step"></span>
                                <span class="step"></span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






<?php get_footer(); ?>
<script>
    
    var currentTab = 0;

    showTab(currentTab);

    var input = document.getElementById("uphone");
                                
    var iti = window.intlTelInput(input, {

        allowDropdown: true,
        initialCountry: "it",
        autoPlaceholder: "polite",
        separateDialCode: true,
        utilsScript: "https://raw.githack.com/jackocnr/intl-tel-input/master/build/js/utils.js"

    });

</script>
