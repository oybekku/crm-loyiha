<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectService;
use App\Models\ProjectStatusLog;
use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArxivBackupService
{
    // ── EKSPORT ──────────────────────────────────────────────────────
    public static function export(array $projectIds): string
    {
        $projects = Project::with([
            'services.assignedUser',
            'payments.createdBy',
            'files',
            'assignedUsers',
            'statusLogs',
        ])->whereIn('id', $projectIds)->get();

        $data = $projects->map(fn($p) => [
            'number'       => $p->number,
            'owner_name'   => $p->owner_name,
            'title'        => $p->title,
            'address'      => $p->address,
            'latitude'     => $p->latitude,
            'longitude'    => $p->longitude,
            'phones'       => $p->phones,
            'description'  => $p->description,
            'category'     => $p->category,
            'status'       => $p->status,
            'total_price'  => $p->total_price,
            'paid_amount'  => $p->paid_amount,
            'deadline_date'=> $p->deadline_date?->toDateString(),
            'created_at'   => $p->created_at?->toISOString(),
            'updated_at'   => $p->updated_at?->toISOString(),

            'assigned_users' => $p->assignedUsers->pluck('email')->toArray(),

            'services' => $p->services->map(fn($s) => [
                'service_name'    => $s->service_name,
                'service_label'   => $s->service_label,
                'price'           => $s->price,
                'discount_type'   => $s->discount_type,
                'discount_value'  => $s->discount_value,
                'final_price'     => $s->final_price,
                'area_m2'         => $s->area_m2,
                'note'            => $s->note,
                'assigned_user_email' => $s->assignedUser?->email,
            ])->toArray(),

            'payments' => $p->payments->map(fn($pay) => [
                'amount'       => $pay->amount,
                'payment_date' => $pay->payment_date?->toDateString(),
                'method'       => $pay->method,
                'note'         => $pay->note,
            ])->toArray(),

            'status_logs' => $p->statusLogs->map(fn($l) => [
                'status'        => $l->status,
                'entered_at'    => $l->entered_at?->toISOString(),
                'left_at'       => $l->left_at?->toISOString(),
                'allocated_days'=> $l->allocated_days,
            ])->toArray(),

            'files' => $p->files->map(fn($f) => [
                'file_name' => $f->file_name,
                'file_path' => $f->file_path,
                'file_type' => $f->file_type,
                'file_size' => $f->file_size,
                'category'  => $f->category,
            ])->toArray(),
        ])->toArray();

        $meta = [
            'version'      => '1.0',
            'exported_at'  => now()->toISOString(),
            'total_projects' => count($data),
            'app'          => 'BestHome CRM',
        ];

        // ZIP yaratish
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);

        $zipPath = $tmpDir . '/arxiv-backup-' . now()->format('Y-m-d-His') . '.zip';

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('projects.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->addFromString('meta.json',     json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Fayllarni qo'shish
        foreach ($projects as $project) {
            foreach ($project->files as $file) {
                $fullPath = Storage::disk('public')->path($file->file_path);
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, 'files/' . $file->file_path);
                }
            }
        }

        $zip->close();
        return $zipPath;
    }

    // ── IMPORT ──────────────────────────────────────────────────────
    public static function importPreview(string $zipPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \Exception('ZIP fayl ochilmadi');
        }

        $json = $zip->getFromName('projects.json');
        $meta = json_decode($zip->getFromName('meta.json') ?? '{}', true);
        $zip->close();

        if (!$json) throw new \Exception('projects.json topilmadi');

        $projects = json_decode($json, true);
        if (!is_array($projects)) throw new \Exception("JSON format xato");

        $preview = [];
        foreach ($projects as $p) {
            $existing = Project::where('number', $p['number'])->first();
            $preview[] = [
                'number'    => $p['number'],
                'owner_name'=> $p['owner_name'],
                'status'    => $p['status'],
                'files'     => count($p['files'] ?? []),
                'payments'  => count($p['payments'] ?? []),
                'exists'    => (bool) $existing,
            ];
        }

        return ['preview' => $preview, 'meta' => $meta, 'total' => count($projects)];
    }

    public static function import(string $zipPath, string $conflict = 'skip'): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \Exception('ZIP fayl ochilmadi');
        }

        $json     = $zip->getFromName('projects.json');
        $projects = json_decode($json, true);

        $imported = 0; $skipped = 0; $updated = 0;

        foreach ($projects as $pData) {
            $existing = Project::where('number', $pData['number'])->first();

            if ($existing && $conflict === 'skip') {
                $skipped++;
                continue;
            }

            $projectData = [
                'number'       => $pData['number'],
                'owner_name'   => $pData['owner_name'],
                'title'        => $pData['title'] ?? null,
                'address'      => $pData['address'] ?? '',
                'latitude'     => $pData['latitude'] ?? null,
                'longitude'    => $pData['longitude'] ?? null,
                'phones'       => $pData['phones'] ?? [],
                'description'  => $pData['description'] ?? null,
                'category'     => $pData['category'] ?? 'turar',
                'status'       => $pData['status'] ?? 'tugallangan',
                'total_price'  => $pData['total_price'] ?? 0,
                'paid_amount'  => $pData['paid_amount'] ?? 0,
                'deadline_date'=> $pData['deadline_date'] ?? null,
            ];

            if ($existing && $conflict === 'overwrite') {
                $existing->update($projectData);
                $project = $existing;
                // Eski bog'liq ma'lumotlarni tozalash
                $project->services()->delete();
                $project->payments()->delete();
                $project->statusLogs()->delete();
                $updated++;
            } else {
                unset($projectData['number']); // auto-generated
                $project = Project::create(array_merge($projectData, ['number' => $pData['number']]));
                $imported++;
            }

            // Xizmatlar
            foreach ($pData['services'] ?? [] as $svc) {
                $project->services()->create([
                    'service_name'   => $svc['service_name'],
                    'service_label'  => $svc['service_label'] ?? $svc['service_name'],
                    'price'          => $svc['price'] ?? 0,
                    'discount_type'  => $svc['discount_type'] ?? 'none',
                    'discount_value' => $svc['discount_value'] ?? 0,
                    'final_price'    => $svc['final_price'] ?? 0,
                    'area_m2'        => $svc['area_m2'] ?? null,
                    'note'           => $svc['note'] ?? null,
                ]);
            }

            // To'lovlar
            foreach ($pData['payments'] ?? [] as $pay) {
                $project->payments()->create([
                    'amount'       => $pay['amount'],
                    'payment_date' => $pay['payment_date'] ?? now()->toDateString(),
                    'method'       => $pay['method'] ?? 'naqd',
                    'note'         => $pay['note'] ?? null,
                    'created_by'   => auth()->id(),
                ]);
            }

            // Status log
            foreach ($pData['status_logs'] ?? [] as $log) {
                $project->statusLogs()->create([
                    'status'         => $log['status'],
                    'entered_at'     => $log['entered_at'] ?? now(),
                    'left_at'        => $log['left_at'] ?? null,
                    'allocated_days' => $log['allocated_days'] ?? null,
                ]);
            }

            // Fayllar
            foreach ($pData['files'] ?? [] as $fileData) {
                $fileInZip = $zip->getFromName('files/' . $fileData['file_path']);
                if ($fileInZip !== false) {
                    $destDir = storage_path('app/public/' . dirname($fileData['file_path']));
                    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                    file_put_contents(
                        storage_path('app/public/' . $fileData['file_path']),
                        $fileInZip
                    );
                }

                // Agar fayl allaqachon DB da yo'q bo'lsa
                if (!$project->files()->where('file_name', $fileData['file_name'])->exists()) {
                    $project->files()->create([
                        'file_name'   => $fileData['file_name'],
                        'file_path'   => $fileData['file_path'],
                        'file_type'   => $fileData['file_type'] ?? null,
                        'file_size'   => $fileData['file_size'] ?? 0,
                        'category'    => $fileData['category'] ?? 'hujjat',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        }

        $zip->close();

        return ['imported' => $imported, 'updated' => $updated, 'skipped' => $skipped];
    }
}
