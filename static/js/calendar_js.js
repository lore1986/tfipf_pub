jQuery(document).ready(function($) {
    
    ajax_call_calendar(3);

    $(document).on('click', '.book-event-action', function(el) {
        var fullid = el.target.id;
        BookEvent(fullid);
    });

    $('#button-no-event-booking').on('click', function() {
        var date = document.getElementById('client_date').value;
        var time = document.getElementById('client_time').value;
        BookNoEvent(date, time);
    });


    function ajax_call_calendar(direction)
    {
        $.ajax({
            url : ajaxurl,
            method: 'POST',
            data: {
                action: 'get_calendar_html',
                //starting_date: currentDate,
                //direction: direction
            },
            success: function(response) {

                $('#container-list-events').html(response);

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function detectMob() {
        return ( ( window.innerWidth <= 800 ) && ( window.innerHeight <= 600 ) );
    }



    function BookEvent(elId)
    {
        $.ajax({
            url : ajaxurl,
            async: false,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'tfIpf_book_check_one',
                bookingid: elId,
            },
            success: function(response) {

                var succeded = response.succeded;
                var htmlToPrint = response.htmlToPrint;
                
                //$('#container-booking').css('display', 'none');

                if(succeded == 1)
                {

                    $('#container-booking').css('display', 'block');
                    $('#container-booking').html(htmlToPrint);

                    
                    $('#container-list-events').attr('class', '');
                    $('#container-list-events').addClass('col-8');
                    $('#container-booking').attr('class', '');
                    $('#container-booking').addClass('col-4');

                    

                    $('#remove-btn').on('click', function() {
                        $('#container-list-events').attr('class', '');
                        $('#container-list-events').addClass('col-12');
                        $('#container-booking').attr('class', '');
                        $('#container-booking').addClass('col-12');
                        $('#container-booking').css('display', 'none');
                    });
                   
                }else
                {
                    $('#container-booking').css('display', 'block');
                    $('#container-booking').html(htmlToPrint);
                }



            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }


    function BookNoEvent(dat, tim)
    {
        $.ajax({
            url : ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'tfIpf_book_check_one',
                bookingdate: dat,
                bookingtime: tim 
            },
            success: function(response) {

                //$('#error-booking-noevent').css('display', 'none');

                var succeded = response.succeded;
                var htmlToPrint = response.htmlToPrint;

                if(succeded == 1)
                {
                    $('#container-booking').html(htmlToPrint);
                }else
                {
                    $('#error-booking-noevent').html(htmlToPrint);
                }
                
                
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    

    

});