<!DOCTYPE html>
<html lang="en">
<head>
    <title>Page Not Found</title>
    <link rel="icon" href="{{ favicon('cropped-Favicon.png') }}">
    <script defer src="{{ assetsUrl('/js/scripts.js') }}"></script>
</head>

<body>
    <h1 style="text-align: center;">
        {{  $_view->err ?? "You don't have permission to perform the action. Contact Administrator for more information" }}
    </h1>
</body>

</html>