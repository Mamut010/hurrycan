<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hello</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <h1> Hello{{ !isNullOrEmpty($name) ? ' ' . $name : ''}}! </h1>
</body>

</html>
