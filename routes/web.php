<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/set-group-id/{groupID}', [GroupController::class, 'setGroupID'])->name('set.group.id');

Route::get('/dashboard', function () {
    return redirect('/admin');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



// Redirect to Google
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
})->name('auth.google');

Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->user();

    // Check if the user exists in the database
    $user = \App\Models\User::where('email', $googleUser->getEmail())->first();

    if (!$user) {
        // If the user does not exist, redirect them with an error message
        return redirect('/login')->with('error', 'No account found with your Google email. Please contact the administrator.');
    }

    // Log the user in
    Auth::login($user);

    // Redirect to the intended page or home
    return redirect('/admin/your-roles');
});


require __DIR__.'/auth.php';
