@if (Session::has('success'))
    <div class="alert alert-success text-center sessionMessage" role="alert">
        <strong>{{ Session::get('success') }}</strong>
    </div>
@endif

@if (Session::has('error'))
    <div class="alert alert-danger text-center sessionMessage" role="alert">
        <strong>{{ Session::get('error') }}</strong>
    </div>
@endif

@if (Session::has('warning'))
    <div class="alert alert-warning text-center sessionMessage" role="alert">
        <strong>{{ Session::get('warning') }}</strong>
    </div>
@endif
<div id="customNotification" style="display:none; padding:10px; margin-bottom:10px; color:white; border-radius:4px;">
</div>
<script>
    function showLoader() {
        $('#loadingOverlay').show();
    }

    function hideLoader() {
        $('#loadingOverlay').hide();
    }

    function showNotification(message, type) {
        var notification = document.getElementById('customNotification');
        notification.textContent = message;

        if (type === 'success') {
            notification.style.backgroundColor = '#28a745'; // Green for success
        } else if (type === 'error') {
            notification.style.backgroundColor = '#dc3545'; // Red for error
        }

        // Show the notification
        notification.style.display = 'block';

        // Hide the notification after 3 seconds (adjust as needed)
        setTimeout(function() {
            notification.style.display = 'none';
        }, 2000);
    }
</script>
