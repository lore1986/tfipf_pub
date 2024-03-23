jQuery(document).ready(function($) {
    
    $('#selectEventImage').click(function(e) {
        e.preventDefault();
        var image = wp.media({
            title: 'Select Image',
            multiple: false
        }).open()
        .on('select', function(e) {
            var uploadedImage = image.state().get('selection').first();
            var imageUrl = uploadedImage.toJSON().url;
            $('#selectedEventImage').val(imageUrl);
            $('#selectedImagePreview').html('<img src="' + imageUrl + '" alt="Selected Image" style="max-width: 300px;">');
        });
    });
    
    $('.select-event-image').click(function(e) {
        e.preventDefault();
        var image = wp.media({
            title: 'Select Image',
            multiple: false
        }).open()
        .on('select', function(e) {
            var uploadedImage = image.state().get('selection').first();
            var imageUrl = uploadedImage.toJSON().url;
            $('#selectedEventImage').val(imageUrl);
            $('#selectedImagePreview').html('<img src="' + imageUrl + '" alt="Selected Image" style="max-width: 300px;">');
        });
    });
});