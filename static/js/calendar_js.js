

function ajax_call_calendar(_maxnum)
{
    jQuery.ajax({
        url : ajaxurl,
        method: 'POST',
        data: {
            action: 'get_calendar_html',
            maxnum: _maxnum
        },
        success: function(response) {

            jQuery('#container-list-events').html(response);

        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
}


function BookNoEvent()
{
    var date = document.getElementById('client_date').value;
    var time = document.getElementById('client_time').value;

    jQuery.ajax({
        url : ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'tfIpf_book_check_one',
            bookingdate: date,
            bookingtime: time 
        },
        success: function(response) {


            var succeded = response.succeded;
            var htmlToPrint = response.htmlToPrint;

            if(succeded == 1)
            {
                jQuery('#container-booking').html(htmlToPrint);
            }else
            {
                jQuery('#error-booking-noevent').html(htmlToPrint);
            }
            
            
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
}


function RetrieveBooking(el)
{
    var _bookingid = el.getAttribute('data-booking-id');

    jQuery.ajax({
        url : ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'tfipf_return_edit_booking_form_ajax',
            bookingid: _bookingid, 
        },
        success: function(response) {

            
            var succeded = response.succeded;
            var htmlToPrint = response.htmlToPrint;

            if(succeded == 1)
            {
                jQuery('#single-booking').html(htmlToPrint);
            }else
            {
                jQuery('#single-booking').html(htmlToPrint);
            }
            
            
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
}

function serializeForm(formId) {
    var form = document.getElementById(formId);
    var formData = [];
    var elements = form.elements;

    for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        if (element.tagName.toLowerCase() !== 'button' && element.name) {
            if (element.type === 'checkbox' || element.type === 'radio') {
                if (element.checked) {
                    formData.push({ name: element.name, value: element.value });
                }
            } else if (element.type === 'select-multiple') {
                for (var j = 0; j < element.options.length; j++) {
                    if (element.options[j].selected) {
                        formData.push({ name: element.name, value: element.options[j].value });
                    }
                }
            } else {
                formData.push({ name: element.name, value: element.value });
            }
        }
    }

    return formData;
}


function save_edit_form_data (){

    var formData = serializeForm('editBookingForm');
    var jsonData = {};

    formData.forEach(element => {
        jsonData[element.name] = element.value;
    });

    
    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'ifpsave_edit_booking', // WordPress action hook
            formData: JSON.stringify(jsonData) // Convert JSON object to string
        },
        success: function(response) {
            console.log(response);
           
        },
        error: function(xhr, status, error) {
            console.error(error);
            
        }
    });
};