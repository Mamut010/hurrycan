<!DOCTYPE html>
<html lang="en">
<head>
    <title>List</title>
</head>

<body>
    <h1> This is the received list </h1>
    <ul>
        @foreach ($list as $value)
            <li>
                {{ $value }}
                {{ "(index = $index, count = $count, even = $even, odd = $odd, " }}
                @if ($value > 2)
                    {{ ">2)" }}
                @else
                    {{ "<=2)" }}
                @endif
            </li>
        @endforeach
    </ul>
</body>

</html>
