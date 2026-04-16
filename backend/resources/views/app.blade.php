<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth" content="{{ auth()->check() ? '1' : '0' }}">
    <title>Балансы</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.ts'])
</head>
<body>
<div id="app"></div>
</body>
</html>
