
// ============================================
// Workflow Management Routes
// ============================================
Route::middleware(['auth', 'role:admin|HR'])->prefix('admin')->group(function () {
    Route::get('/workflows', [App\Http\Controllers\Admin\WorkflowController::class, 'index'])->name('admin.workflows.index');
    Route::post('/workflows/init', [App\Http\Controllers\Admin\WorkflowController::class, 'init'])->name('admin.workflows.init');
    Route::post('/workflows', [App\Http\Controllers\Admin\WorkflowController::class, 'store'])->name('admin.workflows.store');
    Route::put('/workflows/{workflow}', [App\Http\Controllers\Admin\WorkflowController::class, 'update'])->name('admin.workflows.update');
    Route::delete('/workflows/{workflow}', [App\Http\Controllers\Admin\WorkflowController::class, 'destroy'])->name('admin.workflows.destroy');
    
    // Workflow Stage Routes
    Route::post('/workflows/{workflow}/stages', [App\Http\Controllers\Admin\WorkflowController::class, 'addStage'])->name('admin.workflows.stages.store');
    Route::delete('/workflows/stages/{stage}', [App\Http\Controllers\Admin\WorkflowController::class, 'removeStage'])->name('admin.workflows.stages.destroy');
});
