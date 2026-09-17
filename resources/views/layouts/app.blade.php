<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Manager</title>

    @include('libraries.styles')
</head>

<body class="bg-light">
    @yield('content')

    @include('libraries.scripts')
</body>

</html>
