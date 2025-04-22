jQuery(document).ready(function($) {
    // Handle media uploader for room gallery
    $('#g9x_room_gallery_button').on('click', function(e) {
        e.preventDefault();
        
        var galleryFrame = wp.media({
            title: 'Select or Upload Room Images',
            button: {
                text: 'Use these images'
            },
            multiple: true
        });
        
        galleryFrame.on('select', function() {
            var attachments = galleryFrame.state().get('selection').toJSON();
            var galleryIds = $('#g9x_room_gallery').val();
            var idsArray = galleryIds ? galleryIds.split(',') : [];
            var galleryPreview = $('.g9x_room_gallery_preview');
            
            $.each(attachments, function(index, attachment) {
                if (!idsArray.includes(attachment.id.toString())) {
                    idsArray.push(attachment.id);
                    
                    // Add preview image
                    var imageHtml = '<div class="g9x_room_gallery_image" data-image-id="' + attachment.id + '">' +
                                    '<img src="' + attachment.sizes.thumbnail.url + '" alt="Room Gallery Image" />' +
                                    '<span class="g9x_remove_image">×</span>' +
                                    '</div>';
                    galleryPreview.append(imageHtml);
                }
            });
            
            // Update hidden field with comma-separated IDs
            $('#g9x_room_gallery').val(idsArray.join(','));
        });
        
        galleryFrame.open();
    });
    
    // Handle removing images from gallery
    $(document).on('click', '.g9x_remove_image', function() {
        var imageContainer = $(this).parent();
        var imageId = imageContainer.data('image-id').toString();
        var galleryIds = $('#g9x_room_gallery').val();
        var idsArray = galleryIds.split(',');
        
        // Remove ID from array
        idsArray = idsArray.filter(function(id) {
            return id !== imageId;
        });
        
        // Update hidden field
        $('#g9x_room_gallery').val(idsArray.join(','));
        
        // Remove preview image
        imageContainer.fadeOut(300, function() {
            $(this).remove();
        });
    });
    
    // Initialize datepicker for date fields
    if ($.fn.datepicker) {
        $('.g9x-date-field').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    }
    
    // Handle unavailable dates field with date picker
    if ($.fn.datepicker) {
        var unavailableDates = [];
        
        // Parse existing unavailable dates
        var rawDates = $('#g9x_unavailable_dates').val();
        if (rawDates) {
            unavailableDates = rawDates.split(',').map(function(date) {
                return date.trim();
            });
        }
        
        // Initialize date picker for selecting unavailable dates
        $('#g9x_add_unavailable_date').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            minDate: 0, // Only future dates
            onSelect: function(dateText) {
                if (!unavailableDates.includes(dateText)) {
                    unavailableDates.push(dateText);
                    
                    // Sort dates chronologically
                    unavailableDates.sort();
                    
                    // Update hidden field
                    $('#g9x_unavailable_dates').val(unavailableDates.join(', '));
                    
                    // Update visual list
                    renderUnavailableDates();
                }
                
                // Reset datepicker
                $(this).val('');
            }
        });
        
        // Function to render unavailable dates as tags
        function renderUnavailableDates() {
            var container = $('#g9x_unavailable_dates_list');
            container.empty();
            
            $.each(unavailableDates, function(index, date) {
                var tag = $('<span class="g9x-date-tag">' + date +
                           '<span class="g9x-remove-date" data-date="' + date + '">×</span></span>');
                container.append(tag);
            });
        }
        
        // Handle removing dates
        $(document).on('click', '.g9x-remove-date', function() {
            var dateToRemove = $(this).data('date');
            unavailableDates = unavailableDates.filter(function(date) {
                return date !== dateToRemove;
            });
            
            // Update hidden field
            $('#g9x_unavailable_dates').val(unavailableDates.join(', '));
            
            // Update visual list
            renderUnavailableDates();
        });
        
        // Initial render
        renderUnavailableDates();
    }
});