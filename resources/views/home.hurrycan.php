<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home</title>
    <link rel="icon" href="{{ favicon('favicon.png') }}">
    <script defer src="{{ assetsUrl('/js/home.js') }}"></script>
</head>

<body>
    {{-- This is a template comment and won't be shown on the final View --}}
    {{--
        This is a multi-line comment and won't be shown on the final View
    --}}
    <!-- This is a HTML comment and will be shown on the final View -->
    <h1> This is the Homepage </h1>
    <p>
        <button type="button" id="decrease-btn">Decrease</button>
        <button type="button" id="increase-btn">Increase</button>
        <br>
        <span id="number"></span>
    </p>
</body>

</html>
