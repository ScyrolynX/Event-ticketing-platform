<?php

use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Route;

/*
| Everything is React. No Blade views exist in this project at all —
| this single catch-all builds the SPA shell as a plain PHP string and
| serves it for every non-API route; App.jsx's router takes it from there.
*/
Route::get('/{any?}', function () {
    $vite = app(Vite::class);
    $tags = $vite(['resources/css/app.css', 'resources/js/app.jsx']);

    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ScyrolynX</title>
    {$tags}
</head>
<body class="bg-slate-950">
    <div id="root"></div>
</body>
</html>
HTML;

    return response($html);
})->where('any', '.*');
