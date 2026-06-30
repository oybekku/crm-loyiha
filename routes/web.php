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

// Ommaviy chegirma tekshirish sahifasi (flayerdagi QR shu yerga ulanadi — login shart emas)
Route::get('/chegirma/{number}', function (string $number) {
    $project = \App\Models\Project::where('number', '#' . ltrim($number, '#'))
        ->orWhere('number', $number)
        ->firstOrFail();

    $discount   = 7;
    $validUntil = $project->created_at->copy()->addMonth();
    $active     = now()->lte($validUntil);

    return view('chegirma.check', [
        'client'     => $project->owner_name,
        'number'     => $project->number,
        'code'       => $number,
        'discount'   => $discount,
        'openedAt'   => $project->created_at->format('d.m.Y'),
        'validUntil' => $validUntil->format('d.m.Y'),
        'active'     => $active,
    ]);
})->name('chegirma.check');

// Pechat muharriri uchun kutubxonalar (CDN o'rniga o'z serverdan — tez, kesh bilan).
// public/ papkadan o'qiladi (public_html'dan mustaqil), brauzer keshlaydi.
Route::get('/pechat-asset/{name}', function (string $name) {
    $map = [
        'pdf.js'        => ['js/pechat/pdf.min.js',        'application/javascript'],
        'pdf.worker.js' => ['js/pechat/pdf.worker.min.js', 'application/javascript'],
        'pdf-lib.js'    => ['js/pechat/pdf-lib.min.js',    'application/javascript'],
        'stamp.png'     => ['images/pechat.png',           'image/png'],
    ];
    abort_unless(isset($map[$name]), 404);
    [$rel, $mime] = $map[$name];
    $path = public_path($rel);
    abort_unless(is_file($path), 404);
    return response(file_get_contents($path), 200, [
        'Content-Type'  => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->name('pechat.asset');

Route::middleware(['auth'])->group(function () {
    Route::get('/print/project/{project}/ariza', function (\App\Models\Project $project) {
        return view('print.ariza', compact('project'));
    })->name('print.project.ariza');

    // Chegirma flayeri (A4 da 3 ta)
    Route::get('/print/project/{project}/chegirma', function (\App\Models\Project $project) {
        return view('print.chegirma', compact('project'));
    })->name('print.project.chegirma');

    // ── PDF ga qo'lda pechat urish (faqat admin) ──
    Route::get('/pechat/{file}', function (\App\Models\ProjectFile $file) {
        abort_unless(auth()->user()?->isAdmin(), 403);
        return view('pechat.editor', [
            'file'    => $file,
            'pdfUrl'  => route('pechat.pdf', $file),
            'saveUrl' => route('pechat.save', $file),
        ]);
    })->name('pechat.editor');

    Route::get('/pechat/{file}/pdf', function (\App\Models\ProjectFile $file) {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        abort_unless($disk->exists($file->file_path), 404);
        return response($disk->get($file->file_path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
        ]);
    })->name('pechat.pdf');

    Route::post('/pechat/{file}/save', function (\Illuminate\Http\Request $request, \App\Models\ProjectFile $file) {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $b64 = $request->input('pdf');
        if (!$b64) return response()->json(['ok' => false, 'message' => "Bo'sh ma'lumot"]);

        $bytes = base64_decode($b64, true);
        if ($bytes === false || strlen($bytes) < 100) {
            return response()->json(['ok' => false, 'message' => "Noto'g'ri PDF"]);
        }

        $disk    = \Illuminate\Support\Facades\Storage::disk('public');
        $base    = pathinfo($file->file_name, PATHINFO_FILENAME);
        $newName = 'pechatli_' . $base . '.pdf';
        $newPath = 'project-files/' . $file->project_id . '/' . \Illuminate\Support\Str::random(6) . '_' . $newName;
        $disk->put($newPath, $bytes);

        $newFile = \App\Models\ProjectFile::create([
            'project_id'  => $file->project_id,
            'file_name'   => $newName,
            'file_path'   => $newPath,
            'file_type'   => 'application/pdf',
            'file_size'   => strlen($bytes),
            'category'    => 'hujjat',
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json(['ok' => true, 'name' => $newName, 'id' => $newFile->id]);
    })->name('pechat.save');

    Route::get('/print/project/{project}/obloshka', function (\App\Models\Project $project) {
        return view('print.obloshka', compact('project'));
    })->name('print.project.obloshka');

    Route::post('/print/project/{project}/obloshka/manzil', function (\Illuminate\Http\Request $request, \App\Models\Project $project) {
        $project->update(['oblozhka_address' => trim((string) $request->input('manzil'))]);
        return response()->json(['ok' => true]);
    })->name('print.project.obloshka.save');
});
