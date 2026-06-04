<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/house-anim', function () {
    return response()->file(public_path('house-anim.html'));
});

// Ommaviy loyiha holati sahifasi (login shart emas)
Route::get('/track/{number}', function (string $number) {
    $project = \App\Models\Project::where('number', '#' . $number)
        ->orWhere('number', $number)
        ->firstOrFail();

    $allStatuses = \App\Models\ProjectStatus::allOrdered()->where('is_archive', false);

    // Har bir status uchun kirish sanasini log dan olamiz
    $logs = \App\Models\ProjectStatusLog::where('project_id', $project->id)
        ->orderBy('entered_at')
        ->get()
        ->keyBy('status');

    $currentStatus = $project->status;

    $timeline = $allStatuses->map(function ($ps) use ($logs, $currentStatus) {
        $log = $logs->get($ps->key);
        $state = 'kutilmoqda';
        $date  = null;
        if ($log) {
            $date  = $log->entered_at?->format('d.m.Y');
            $state = ($ps->key === $currentStatus && !$log->left_at) ? 'jarayonda' : 'qilindi';
        }
        return ['key' => $ps->key, 'label' => $ps->label, 'state' => $state, 'date' => $date];
    });

    return view('track.status', compact('project', 'timeline', 'currentStatus'));
})->name('track.project');

Route::middleware(['auth'])->group(function () {
    Route::get('/print/project/{project}/ariza', function (\App\Models\Project $project) {
        return view('print.ariza', compact('project'));
    })->name('print.project.ariza');

    Route::get('/print/project/{project}/obloshka', function (\App\Models\Project $project) {
        return view('print.obloshka', compact('project'));
    })->name('print.project.obloshka');

    Route::post('/print/project/{project}/obloshka/manzil', function (\Illuminate\Http\Request $request, \App\Models\Project $project) {
        $project->update(['oblozhka_address' => trim((string) $request->input('manzil'))]);
        return response()->json(['ok' => true]);
    })->name('print.project.obloshka.save');
});
