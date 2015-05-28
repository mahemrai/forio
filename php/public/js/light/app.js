/**
 * app.js
 * Contains javascript for light theme.
 * @author Mahendra Rai
 */
$(document).ready(function() {
    $('.loader').hide();

    /**
     * Fetch image data from the server when clicked on image.
     */
    $('.load-image').click(function(e) {
        e.preventDefault();

        $('.preview').fadeOut(500, function() {
            $('.loader').show();
        });

        $.get('/image/' + $(this).data('id'))
            .done(function(response) {
                if (response.success) {
                    $('.image-box').attr('src', response.data.filename);
                    $('#image-title').text(response.data.title);
                    $('#image-desc').text(response.data.description);

                    $('.loader').hide(0, function() {
                        $('.preview').fadeIn();
                    });
                } else {
                    alert(response.message);
                }
        });
    });
});