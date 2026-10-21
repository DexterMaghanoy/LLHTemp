<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;

// Auth routes
Route::get('/signin', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/signin', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Authenticated area
Route::middleware('auth')->group(function () {
    Route::get('/', function (Illuminate\Http\Request $request) {
        $period = in_array($request->query('period'), ['daily', 'weekly', 'monthly'], true)
            ? $request->query('period')
            : 'daily';

        $now = now();
        if ($period === 'weekly') {
            $start = $now->copy()->startOfWeek(Carbon\Carbon::SUNDAY)->max($now->copy()->startOfMonth());
            $end = $now->copy()->endOfWeek(Carbon\Carbon::SATURDAY)->min($now->copy()->endOfMonth());
        } elseif ($period === 'monthly') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        } else {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
        }

        return view('pages.dashboard.tickets', [
            'title' => 'Hotel Ticketing Dashboard',
            'period' => $period,
            'rangeStart' => $start,
            'rangeEnd' => $end,
        ]);
    })->name('dashboard');

    Route::resource('tickets', App\Http\Controllers\TicketController::class)->except(['edit']);
    Route::post('tickets/{ticket}/comments', [App\Http\Controllers\TicketController::class, 'storeComment'])->name('tickets.comments.store');
    Route::patch('tickets/{ticket}/status', [App\Http\Controllers\TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::patch('tickets/{ticket}/assign', [App\Http\Controllers\TicketController::class, 'assign'])->name('tickets.assign');

    Route::middleware('admin')->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class)->except(['show']);
    });
});

// Guest-only redirects: keep legacy demo routes accessible
Route::get('/calendar', function () { return view('pages.calender', ['title' => 'Calendar']); })->name('calendar');
Route::get('/profile', function () { return view('pages.profile', ['title' => 'Profile']); })->name('profile');
Route::get('/form-elements', function () { return view('pages.form.form-elements', ['title' => 'Form Elements']); })->name('form-elements');
Route::get('/basic-tables', function () { return view('pages.tables.basic-tables', ['title' => 'Basic Tables']); })->name('basic-tables');
Route::get('/blank', function () { return view('pages.blank', ['title' => 'Blank']); })->name('blank');
Route::get('/error-404', function () { return view('pages.errors.error-404', ['title' => 'Error 404']); })->name('error-404');
Route::get('/line-chart', function () { return view('pages.chart.line-chart', ['title' => 'Line Chart']); })->name('line-chart');
Route::get('/bar-chart', function () { return view('pages.chart.bar-chart', ['title' => 'Bar Chart']); })->name('bar-chart');
Route::get('/alerts', function () { return view('pages.ui-elements.alerts', ['title' => 'Alerts']); })->name('alerts');
Route::get('/avatars', function () { return view('pages.ui-elements.avatars', ['title' => 'Avatars']); })->name('avatars');
Route::get('/badge', function () { return view('pages.ui-elements.badges', ['title' => 'Badges']); })->name('badges');
Route::get('/buttons', function () { return view('pages.ui-elements.buttons', ['title' => 'Buttons']); })->name('buttons');
Route::get('/image', function () { return view('pages.ui-elements.images', ['title' => 'Images']); })->name('images');
Route::get('/videos', function () { return view('pages.ui-elements.videos', ['title' => 'Videos']); })->name('videos');

// Locale Switch Route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// pages

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');


// authentication pages (handled by AuthController above)

// ui elements pages
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');






















