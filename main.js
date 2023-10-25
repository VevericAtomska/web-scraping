$(document).ready(function() {
    $('#downloadButton').on('click', function() {
        $.ajax({
            type: 'GET',
            url: 'download.php',
            data: { action: 'download'
            }, success: function(response) {
                location.reload();
                window.location.href = response;
            }, error: function(errorData) {

                console.log('An error occurred: ' + errorData);
            }
        });
    });
});
