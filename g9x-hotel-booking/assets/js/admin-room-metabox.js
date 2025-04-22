jQuery(document).ready(function($) {
    // --- Room Gallery Metabox Script ---

    var galleryInput = $('#g9x_room_gallery');
    var galleryPreview = $('.g9x_room_gallery_preview');
    var galleryButton = $('#g9x_room_gallery_button');

    // Handle opening the media uploader
    galleryButton.on('click', function(e) {
        e.preventDefault();

        // If the frame already exists, reopen it
        if (galleryFrame) {
            galleryFrame.open();
            return;
        }

        // Create a new media frame
        var galleryFrame = wp.media({
            title: wp.media.view.l10n.selectOrUploadMediaTitle, // Use WP's default title
            button: {
                text: wp.media.view.l10n.useThisMedia // Use WP's default button text
            },
            library: {
                type: 'image' // Only allow images
            },
            multiple: true // Allow multiple image selection
        });

        // When images are selected, run a callback.
        galleryFrame.on('select', function() {
            var attachments = galleryFrame.state().get('selection').toJSON();
            var currentIds = galleryInput.val() ? galleryInput.val().split(',') : [];

            $.each(attachments, function(index, attachment) {
                // Ensure ID is a string for comparison and doesn't already exist
                var attachmentIdStr = attachment.id.toString();
                if (!currentIds.includes(attachmentIdStr)) {
                    currentIds.push(attachmentIdStr);

                    // Add preview image (use thumbnail size)
                    var thumbnailUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    var imageHtml = `
                        <div class="g9x_room_gallery_image" data-image-id="${attachment.id}">
                            <img src="${thumbnailUrl}" alt="${attachment.alt || ''}" />
                            <span class="g9x_remove_image" title="${wp.media.view.l10n.remove || 'Remove image'}">×</span>
                        </div>`;
                    galleryPreview.append(imageHtml);
                }
            });

            // Update hidden field with comma-separated IDs
            galleryInput.val(currentIds.join(',')).trigger('change'); // Trigger change event if needed
        });

        // Finally, open the modal
        galleryFrame.open();
    });

    // Handle removing images from the gallery preview
    galleryPreview.on('click', '.g9x_remove_image', function() {
        var imageContainer = $(this).closest('.g9x_room_gallery_image');
        var imageIdToRemove = imageContainer.data('image-id').toString();
        var currentIds = galleryInput.val() ? galleryInput.val().split(',') : [];

        // Remove the ID from the array
        var updatedIds = currentIds.filter(function(id) {
            return id !== imageIdToRemove;
        });

        // Update hidden field
        galleryInput.val(updatedIds.join(',')).trigger('change');

        // Remove preview image with fade effect
        imageContainer.fadeOut(300, function() {
            $(this).remove();
        });
    });

    // Optional: Make gallery preview sortable (requires jQuery UI Sortable)
    if ($.fn.sortable) {
        galleryPreview.sortable({
            items: '.g9x_room_gallery_image',
            cursor: 'move',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            forceHelperSize: false,
            helper: 'clone',
            opacity: 0.65,
            placeholder: 'g9x-gallery-sortable-placeholder',
            start: function(event, ui) {
                ui.item.css('background-color', '#f6f6f6');
            },
            stop: function(event, ui) {
                ui.item.removeAttr('style');
            },
            update: function() {
                var sortedIds = [];
                galleryPreview.find('.g9x_room_gallery_image').each(function() {
                    sortedIds.push($(this).data('image-id').toString());
                });
                galleryInput.val(sortedIds.join(',')).trigger('change');
            }
        });
        // Add placeholder style if using sortable
        $(document.head).append('<style>.g9x-gallery-sortable-placeholder { border: 1px dashed #ccc; background-color: #f9f9f9; width: 80px; height: 80px; float: left; margin: 5px; box-sizing: border-box; }</style>');
    }

});