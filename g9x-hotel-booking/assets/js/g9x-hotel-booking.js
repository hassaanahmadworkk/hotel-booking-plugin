jQuery(document).ready(function($) {

    // --- Search Form Enhancements ---

    // Initialize Select2 for the location dropdown
    if ($('#g9x_search_location').length && $.fn.select2) {
        $('#g9x_search_location').select2({
            placeholder: "Select Location", // More appropriate placeholder
            allowClear: true,
            width: '100%' // Ensure it takes full width of container
        });
    }

    // Initialize Datepickers for Check-in/Check-out
    if ($.fn.datepicker) {
        var dateFormat = "yy-mm-dd"; // Standard format

        var checkin = $("#g9x_search_check_in").datepicker({
            dateFormat: dateFormat,
            minDate: 0, // Minimum date is today
            changeMonth: true,
            numberOfMonths: 1 // Show one month
        })
        .on("change", function() {
            // Set the minDate for check-out based on check-in
            checkout.datepicker("option", "minDate", getDate(this));
        });

        var checkout = $("#g9x_search_check_out").datepicker({
            dateFormat: dateFormat,
            minDate: 0, // Set dynamically based on check-in
            changeMonth: true,
            numberOfMonths: 1
        })
        .on("change", function() {
            // Optional: Set maxDate for check-in based on check-out
            // checkin.datepicker( "option", "maxDate", getDate( this ) );
        });

        // Helper function to parse date from datepicker input
        function getDate(element) {
            var date;
            try {
                date = $.datepicker.parseDate(dateFormat, element.value);
            } catch (error) {
                date = null;
            }
            // If date is valid, add one day for the minDate of the checkout
            if (date) {
                date.setDate(date.getDate() + 1);
            }
            return date;
        }
    }

    // --- Booking Popup Handling ---

    var $bookingPopup = $('#g9x-booking-popup');
    var $bookingForm = $('#g9x-booking-form');
    var $bookingNotice = $('#g9x-booking-form-notice');
    var $submitButton = $bookingForm.find('.g9x-confirm-booking');
    var $spinner = $bookingForm.find('.g9x-spinner');

    // Open popup when Book Now is clicked
    $('.g9x-book-now-button').click(function(e) {
        e.preventDefault();

        var $button = $(this);
        var roomTitle = $button.data('room-title');
        var checkIn = $button.data('check-in');
        var checkOut = $button.data('check-out');
        var adults = $button.data('adults');
        var children = $button.data('children');
        var price = parseFloat($button.data('price')).toFixed(2); // Ensure float with 2 decimals
        var nights = $button.data('nights');

        // Set hidden field values
        $bookingForm.find('#g9x_booking_room_id').val($button.data('room-id'));
        $bookingForm.find('#g9x_booking_check_in').val(checkIn);
        $bookingForm.find('#g9x_booking_check_out').val(checkOut);
        $bookingForm.find('#g9x_number_of_guests_adults').val(adults);
        $bookingForm.find('#g9x_number_of_guests_children').val(children);
        $bookingForm.find('#g9x_total_price').val(price);

        // Update booking summary (currency symbol handled server-side or via settings)
        $bookingPopup.find('#g9x-booking-room-title').text('Room: ' + roomTitle);
        $bookingPopup.find('#g9x-booking-dates').text('Dates: ' + checkIn + ' to ' + checkOut + ' (' + nights + ' nights)');
        $bookingPopup.find('#g9x-booking-guests').text('Guests: ' + adults + ' Adults, ' + children + ' Children');
        $bookingPopup.find('#g9x-booking-price').text('Total Price: ' + price); // Removed hardcoded '$'

        // Clear previous notices and show popup
        $bookingNotice.hide().removeClass('g9x-success g9x-error').empty();
        $submitButton.prop('disabled', false); // Re-enable button
        $spinner.hide();
        $bookingPopup.fadeIn(200);
    });

    // Close popup when X is clicked
    $('.g9x-close-popup').click(function() {
        $bookingPopup.fadeOut(200);
    });

    // Close popup when clicking outside the content
    $bookingPopup.click(function(e) {
        // Check if the click is directly on the popup background
        if ($(e.target).is($bookingPopup)) {
            $bookingPopup.fadeOut(200);
        }
    });

    // --- AJAX Booking Form Submission ---
    $bookingForm.on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        $bookingNotice.hide().removeClass('g9x-success g9x-error').empty(); // Clear previous notices
        $submitButton.prop('disabled', true); // Disable button
        $spinner.show(); // Show spinner

        // Basic client-side validation (optional, server-side is crucial)
        var email = $bookingForm.find('#g9x_booking_email').val();
        if (email && !/\S+@\S+\.\S+/.test(email)) { // Simple email format check
             $bookingNotice.addClass('g9x-error').text('Please enter a valid email address.').show();
             $submitButton.prop('disabled', false);
             $spinner.hide();
             return; // Stop submission
        }
        // Add other simple checks if needed (e.g., required fields)

        // Prepare form data for AJAX
        var formData = $(this).serialize();

        // AJAX request
        $.ajax({
            type: 'POST',
            url: g9x_booking_params.ajax_url, // Localized URL
            data: formData, // Includes action and nonce from hidden fields
            dataType: 'json', // Expect JSON response from server
            success: function(response) {
                if (response.success) {
                    // Success! Show message, maybe clear form, maybe redirect after delay
                    $bookingNotice.addClass('g9x-success').html(response.data.message || 'Booking successful!').show();
                    $bookingForm[0].reset(); // Clear the form fields
                    // Optionally redirect after a delay
                    if (response.data.redirect_url) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect_url;
                        }, 2000); // Redirect after 2 seconds
                    } else {
                         // Keep popup open or close it after delay
                         setTimeout(function() {
                             $bookingPopup.fadeOut(200);
                         }, 3000);
                    }
                } else {
                    // Error reported by server
                    $bookingNotice.addClass('g9x-error').html(response.data.message || g9x_booking_params.error_message).show();
                    $submitButton.prop('disabled', false); // Re-enable button on error
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // AJAX request itself failed (network error, server error 500, etc.)
                console.error("AJAX Error:", textStatus, errorThrown);
                $bookingNotice.addClass('g9x-error').html(g9x_booking_params.error_message + ' (AJAX Error)').show();
                $submitButton.prop('disabled', false); // Re-enable button
            },
            complete: function() {
                $spinner.hide(); // Hide spinner regardless of outcome
            }
        });
    });

});