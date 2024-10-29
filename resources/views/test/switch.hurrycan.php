<!DOCTYPE html>
<html lang="en">
<head>
    <title>Switch</title>
</head>

<body>
    <h1>Received [{{ $value }}]</h1>
    <p>
        @switch ($value)
        @case (1)
            1
            @break
        @case (2)
            2
            @break
        @default
            Other
            @break
        @endswitch
    </p>
</body>

</html>
