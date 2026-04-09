<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
        <a class="navbar-brand" href="{{ route('posts.index') }}">PostsApp</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            @auth
                <span class="text-white">{{ auth()->user()->name }}</span>
                <span class="badge bg-info">{{ auth()->user()->getRoleNames()->first() }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            @endauth
        </div>
    </nav>

    <div class="container mt-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
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
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
