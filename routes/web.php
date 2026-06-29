<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// iOS Safari ba'zan login formani Livewire (AJAX) orqali emas, balki to'g'ridan-to'g'ri
// "native" POST qilib yuboradi (sahifa bfcache'dan tiklanganda yoki klaviaturadagi "Go"
// tugmasi JS ulgurmay bosilganda). Bunda /admin/login GET-only bo'lgani uchun 500 xato
// (MethodNotAllowedHttpException) chiqadi. Shu holatda foydalanuvchini login sahifasiga
// muloyim qaytaramiz — xato o'rniga shunchaki qayta urinadi.
Route::post('/admin/login', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/house-anim', function () {
    return response()->file(public_path('house-anim.html'));
});

// Bolalar Boshqaruvi ilovasi uchun maxfiylik siyosati (Google Play)
Route::get('/privacy-policy', function () {
    return response()->file(public_path('privacy-policy.html'));
})->name('privacy.policy');

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
