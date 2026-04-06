<?php

// TEMPORARY TEST ROUTE - Remove after debugging
Route::get('/test-lms-access', function() {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }
    
    return response()->json([
        'user' => $user->name,
        'email' => $user->email,
        'roles' => $user->roles->pluck('name'),
        'has_admin' => $user->roles->contains('name', 'Admin'),
        'has_hr' => $user->roles->contains('name', 'HR'),
        'has_super_admin' => $user->roles->contains('name', 'Super Admin'),
        'message' => 'If you see this, authentication is working'
    ]);
})->middleware('auth');
