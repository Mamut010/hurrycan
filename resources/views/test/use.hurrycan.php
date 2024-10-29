<!DOCTYPE html>
<html lang="en">
<head>
    <title>Use</title>
</head>

<body>
    @use("App\Http\Services\UseExampleServiceImpl")
    @use('App\Http\Services\UseExampleService', "SomeClass")

    @php
        $service = new UseExampleServiceImpl(100);
        $someClass = new SomeClass(-10);
    @endphp

    <h1>
        service->x = {{ $service->x }}
    </h1>
    <h1>
        someClass->x = {{ $someClass->x }}
    </h1>
</body>

</html>
