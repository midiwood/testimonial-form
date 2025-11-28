jQuery(document).ready(function($) {
    const profilePicInput = $('#profilePic');
    const profilePreview = $('#profilePreview');
    const testimonialInput = $('#testimonial');
    const charCount = $('#charCount');
    const form = $('#testimonialForm');
    const successMessage = $('#successMessage');
    const errorMessage = $('#errorMessage');
    const submitBtn = form.find('.submit-btn');
    const starRating = $('#starRating');
    const ratingValue = $('#ratingValue');
    const stars = starRating.find('.testimonial-star');
    const nameInput = $('#nameInput');
    const titleInput = $('#titleInput');
    const thankYouScreen = $('#thankYouScreen');

    let uploadedImageId = null;
    let currentRating = 0;

    // Read parameters from URL hash
    function getHashParams() {
        const hash = window.location.hash.substring(1);
        const params = {};
        if (hash) {
            hash.split('&').forEach(function(part) {
                const item = part.split('=');
                params[item[0]] = decodeURIComponent(item[1]);
            });
        }
        return params;
    }

    // Pre-fill form from hash parameters
    const hashParams = getHashParams();
    if (hashParams.name) {
        nameInput.val(hashParams.name);
    }
    if (hashParams.title) {
        titleInput.val(hashParams.title);
    }

    // Star rating functionality
    stars.each(function(index) {
        const star = $(this);
        
        star.on('click', function(e) {
            const rect = this.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const starWidth = rect.width;
            const starValue = index + 1;
            
            if (clickX < starWidth / 2) {
                currentRating = starValue - 0.5;
            } else {
                currentRating = starValue;
            }
            
            updateStarDisplay();
        });

        star.on('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const hoverX = e.clientX - rect.left;
            const starWidth = rect.width;
            const starValue = index + 1;
            
            let previewRating;
            if (hoverX < starWidth / 2) {
                previewRating = starValue - 0.5;
            } else {
                previewRating = starValue;
            }
            
            updateStarDisplay(previewRating);
        });

        star.on('mouseleave', function() {
            updateStarDisplay();
        });
    });

    function updateStarDisplay(previewRating) {
        const rating = previewRating !== undefined ? previewRating : currentRating;
        
        stars.each(function(index) {
            const star = $(this);
            const starValue = index + 1;
            star.removeClass('active half');
            
            if (starValue <= Math.floor(rating)) {
                star.addClass('active');
            } else if (starValue === Math.ceil(rating) && rating % 1 !== 0) {
                star.addClass('half');
            }
        });
        
        if (previewRating === undefined) {
            ratingValue.text(currentRating > 0 ? `${currentRating} star${currentRating !== 1 ? 's' : ''}` : '0 stars');
        } else {
            ratingValue.text(`${previewRating} star${previewRating !== 1 ? 's' : ''}`);
        }
    }

    // Profile picture upload
    profilePicInput.on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.html(`<img src="${e.target.result}" alt="Profile">`);
            };
            reader.readAsDataURL(file);

            // Upload to WordPress
            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'upload_testimonial_image');
            formData.append('nonce', testimonialAjax.nonce);

            $.ajax({
                url: testimonialAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        uploadedImageId = response.data.id;
                    } else {
                        console.error('Image upload failed:', response.data.message);
                    }
                },
                error: function() {
                    console.error('Image upload error');
                }
            });
        }
    });

    // Character count
    testimonialInput.on('input', function() {
        charCount.text(this.value.length);
    });

    // Form submission
    form.on('submit', function(e) {
        e.preventDefault();
        
        successMessage.hide();
        errorMessage.hide();
        
        const testimonial = testimonialInput.val().trim();
        const name = $('#nameInput').val().trim();
        const title = $('#titleInput').val().trim();
        
        // Validation
        if (!name) {
            errorMessage.text('⚠ Please enter your name!').show();
            return;
        }

        if (!title) {
            errorMessage.text('⚠ Please enter your title!').show();
            return;
        }

        if (currentRating === 0) {
            errorMessage.text('⚠ Please select a star rating!').show();
            return;
        }

        if (!testimonial) {
            errorMessage.text('⚠ Please enter your testimonial!').show();
            return;
        }

        if (!uploadedImageId) {
            errorMessage.text('⚠ Please upload a profile picture!').show();
            return;
        }

        // Disable button and show loading
        submitBtn.prop('disabled', true);
        const originalText = submitBtn.text();
        submitBtn.html('<span class="loading"></span> Submitting...');

        // Submit via AJAX
        $.ajax({
            url: testimonialAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'submit_testimonial',
                nonce: testimonialAjax.nonce,
                name: name,
                title: title,
                testimonial: testimonial,
                rating: currentRating,
                profile_pic_id: uploadedImageId
            },
            success: function(response) {
                if (response.success) {
                    successMessage.hide();
                    errorMessage.hide();
                    form.addClass('is-hidden');
                    thankYouScreen.attr('aria-hidden', 'false').fadeIn(200);

                    form[0].reset();
                    profilePreview.html('<span class="placeholder">👤</span>');
                    uploadedImageId = null;
                    currentRating = 0;
                    updateStarDisplay();
                    charCount.text('0');
                } else {
                    errorMessage.text(`⚠ ${response.data.message}`).show();
                }
            },
            error: function() {
                errorMessage.text('⚠ An error occurred. Please try again.').show();
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.text(originalText);
            }
        });
    });
});