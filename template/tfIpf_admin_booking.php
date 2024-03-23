<?php

    /* Template Name: Calendario Prenotazioni */
    //;
    wp_head();
    $daysArr = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

    get_header();

    if( current_user_can('editor') || current_user_can('administrator') ) { 
        echo '<h1>Hello Buddy</h1>';

    }
    else
    {
        echo '<h1>Hello</h1>';
    }

    ?>

<?php get_header();
?>
<style>
.badge-success
{
    background-color: aqua; 
}

.badge-primary
{
    background-color: chocolate;
}
</style>

<div id="primary">
    <div id="content" class="site-content" role="main">
        <div class="container mt-5">
            <div class="row m-2 text-center">
                <div class="col-7">
                    <div class="row">
                        <div class="col-1 offset-3">
                            <button class="btn btn-primary" onclick="ajax_call_calendar(0)"  id="prev-month"> < </button>
                        </div>
                        <div class="col-4">
                            <p id="date-text-val"> <?php echo date('d-m-Y') ?>  </p>
                            <p id="date-val" style="display:none"> <?php echo strtotime(date('d-m-Y')) ?>  </p>
                        </div>
                        <div class="col-1">
                            <button class="btn btn-primary" onclick="ajax_call_calendar(1)" id="next-month"> > </button>
                        </div>

                        <div class="row mt-2">
                            <?php
                                foreach ($daysArr as $day) { ?>
                                    <div class="col border">
                                        <p class="my-auto"> <?php  echo $day  ?>  </p>
                                    </div>
                            <?php   } 
                            ?>
                        </div>
                        <div class="row" id="calendar-container">
                    
                        </div>
                    </div>
                </div>
                <div class="col-5">
                    <div class="row">
                        <h4>Le Prenotazioni</h4>
                    </div>
                    <div class="row" id="calendar-bookings">
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





<?php get_footer(); ?>

<script>

    window.onload = function() {
        ajax_call_calendar(-1);
    };

    function ajax_call_calendar(direction) {
        var currentDate = document.getElementById('date-val').innerText;
        console.log(currentDate)

        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'tf_ipf_get_admin_calendar',
                datestart: currentDate,
                direction: direction
            },
            success: function(response) {

                var newDate = response.newDate;
                var htmlToPrint = response.htmlToPrint;
                var timestampNew = response.newTimestamp;

                document.getElementById('calendar-container').innerHTML = htmlToPrint;
                document.getElementById('date-text-val').innerText = newDate;
                document.getElementById('date-val').innerText = timestampNew;
                
                var clickableDays = document.querySelectorAll('.clickable-day');
                clickableDays.forEach(function(element) {
                    element.addEventListener('click', function(event) {
                        event.preventDefault();
                        
                        var dataValue = element.getAttribute('data-date');
                        console.log('Clicked day:', dataValue);
                        PrintDayBookings(dataValue)

                    });
                });

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function PrintDayBookings(timestampdate)
    {
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'tf_ipf_get_day_bookings',
                timestampdate: timestampdate,
            },
            success: function(response) {

                document.getElementById('calendar-bookings').innerHTML = response;

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function detectMob() {
        return (window.innerWidth <= 800) && (window.innerHeight <= 600);
    }

        

</script>