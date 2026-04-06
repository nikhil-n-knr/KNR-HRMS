<?php
use Illuminate\Support\Facades\Route;
use App\Models\Project;

Route::get('/debug-projects', function () {
    return Project::all()->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'status' => $p->status,
            'deleted_at' => $p->deleted_at
        ];
    });
});
