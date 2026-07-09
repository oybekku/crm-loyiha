<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectService;
use App\Models\ProjectFile;
use App\Models\ProjectStatus;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Loyiha ma'lumotini tahrirlash modali — ALOHIDA komponent.
 * Shu sababli modal ochilganda butun Kanban doskasi (3.8 MB) qaytadan
 * yuborilmaydi — faqat shu kichik komponent yangilanadi → tez.
 * Amal tugmalari (To'lov, O'tkazish...) parent KanbanBoard'ga event yuboradi.
 */
class ProjectEditModal extends Component
{
    use WithFileUploads;

    public bool   $showEditInfoModal = false;
    public int    $editInfoId        = 0;
    public string $ei_owner          = '';
    public string $ei_title          = '';
    public string $ei_address        = '';
    public string $ei_oblozhka       = '';
    public string $ei_coords         = '';
    public array  $ei_phones         = ['+998'];
    public string $ei_description    = '';
    public string $ei_category       = 'turar';
    public string $ei_passportSeries   = '';
    public string $ei_passportIssuedBy = '';
    public string $ei_pinfl            = '';
    public array  $ei_services       = [];
    public array  $ei_files          = [];
    public $ei_newFiles              = [];
    public array  $ei_genplan        = [];
    public $ei_newGenplan            = [];
    public array  $ei_genplanSel     = [];   // yig'ish uchun belgilangan PDF id lar
    public string $ei_status         = '';
    public string $ei_workStatus     = 'yangi';
    public bool   $ei_isUrgent       = false;
    public bool   $ei_paymentRequested = false;
    public string $ei_mygovLogin     = '';
    public string $ei_mygovPassword  = '';
    public string $ei_mygovFish      = '';
    public bool   $ei_showMygov      = false;
    public string $ei_newSvcType     = '';
    public string $ei_newSvcPrice    = '';
    public $ei_newSvcUser            = null;

    #[On('open-edit-modal')]
    public function openEditInfoModal(int $id): void
    {
        $p = Project::find($id);
        if (!$p) return;
        $this->editInfoId     = $id;
        $this->ei_owner       = $p->owner_name ?? '';
        $this->ei_title       = $p->title ?? '';
        $this->ei_address     = $p->address ?? '';
        $this->ei_oblozhka    = $p->oblozhka_address ?? '';
        $this->ei_coords      = ($p->latitude && $p->longitude) ? ($p->latitude . ', ' . $p->longitude) : '';
        $this->ei_phones      = !empty($p->phones)
            ? array_values(array_map(fn($x) => is_array($x) ? ($x['phone'] ?? '') : (string) $x, $p->phones))
            : ['+998'];
        if (empty($this->ei_phones)) $this->ei_phones = ['+998'];
        $this->ei_description = $p->description ?? '';
        $this->ei_category    = $p->category ?: 'turar';
        $this->ei_passportSeries   = $p->passport_series ?? '';
        $this->ei_passportIssuedBy = $p->passport_issued_by ?? '';
        $this->ei_pinfl            = $p->pinfl ?? '';
        $this->ei_services    = $this->buildEiServices($p);
        $this->ei_files       = $this->buildEiFiles($p, 'hujjat');
        $this->ei_genplan     = $this->buildEiFiles($p, 'genplan');
        $this->ei_newFiles    = [];
        $this->ei_newGenplan  = [];
        $this->ei_genplanSel  = [];
        $this->ei_status      = $p->status;
        $this->ei_workStatus  = $p->work_status ?? 'yangi';
        $this->ei_isUrgent    = (bool) $p->is_urgent;
        $this->ei_paymentRequested = (bool) $p->payment_requested_at;
        $this->ei_mygovLogin    = $this->canMygov($p) ? ($p->mygov_login ?? '') : '';
        $this->ei_mygovPassword = $this->canMygov($p) ? ($p->mygov_password ?? '') : '';
        $this->ei_mygovFish     = $this->canMygov($p) ? ($p->mygov_fish ?? '') : '';
        $this->ei_showMygov     = false;
        $this->showEditInfoModal = true;
    }

    public function closeEditInfoModal(): void
    {
        $this->showEditInfoModal = false;
        $this->editInfoId = 0;
    }

    // ── Amal tugmalari — modalni yopib, parent KanbanBoard'ga event yuboramiz ──
    public function eiGoPayment(): void      { $id = $this->editInfoId; $this->closeEditInfoModal(); $this->dispatch('kb-open-payment', id: $id); }
    public function eiGoRoute(): void        { $id = $this->editInfoId; $s = $this->ei_status; $this->closeEditInfoModal(); $this->dispatch('kb-open-route', id: $id, status: $s); }
    public function eiGoAssign(): void       { $id = $this->editInfoId; $this->closeEditInfoModal(); $this->dispatch('kb-open-assign', id: $id); }
    public function eiRequestPayment(): void { $id = $this->editInfoId; $this->closeEditInfoModal(); $this->dispatch('kb-request-payment', id: $id); }
    public function eiCancelRequest(): void  { $id = $this->editInfoId; $this->closeEditInfoModal(); $this->dispatch('kb-cancel-request', id: $id); }
    public function eiMarkComplete(): void   { $id = $this->editInfoId; $this->closeEditInfoModal(); $this->dispatch('kb-mark-complete', id: $id); }
    public function eiMarkUncomplete(): void { $id = $this->editInfoId; $this->closeEditInfoModal(); $this->dispatch('kb-mark-uncomplete', id: $id); }
    public function eiMove(string $status): void { $id = $this->editInfoId; $this->closeEditInfoModal(); $this->dispatch('kb-move', id: $id, status: $status); }

    // Per-xizmat "Tugallandi/Tugalmagan" toggle — modal ochiq qoladi, ro'yxat va karta yangilanadi
    public function eiToggleService(int $serviceId): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $svc = ProjectService::find($serviceId);
        if (!$svc || $svc->project_id !== $this->editInfoId) return;

        $svc->completed_at = $svc->completed_at ? null : now();
        $svc->saveQuietly();

        $p = Project::find($this->editInfoId);
        if ($p) $this->ei_services = $this->buildEiServices($p);
        $this->dispatch('kb-refresh'); // doskadagi karta ham yangilansin
    }

    // Xizmatga mas'ul hodim biriktirish (admin/menejer) — shu yerda, modal ochiq qoladi
    public function eiAssignService(int $serviceId, $userId): void
    {
        $u = auth()->user();
        if (!$u || !$u->canSeeAllProjects()) {
            $this->dispatch('notify', type: 'error', message: "Ruxsat yo'q");
            return;
        }
        $svc = ProjectService::find($serviceId);
        if (!$svc || $svc->project_id !== $this->editInfoId) return;

        $svc->update(['assigned_user_id' => $userId ? (int) $userId : null]);

        $p = Project::find($this->editInfoId);
        if ($p) $this->ei_services = $this->buildEiServices($p);
        $this->dispatch('kb-refresh');
        $this->dispatch('notify', type: 'success', message: $userId ? "Mas'ul biriktirildi" : "Mas'ul olib tashlandi");
    }

    // Xizmat ish muddati (kun) — admin/menejer belgilaydi. Modal ochiq qoladi.
    public function eiSetServiceDeadline(int $serviceId, $days): void
    {
        $u = auth()->user();
        if (!$u || !$u->canSeeAllProjects()) {
            $this->dispatch('notify', type: 'error', message: "Ruxsat yo'q");
            return;
        }
        $svc = ProjectService::find($serviceId);
        if (!$svc || $svc->project_id !== $this->editInfoId) return;

        $days = trim((string) $days);
        $val  = ($days === '' || (int) $days <= 0) ? null : min(3650, (int) $days);
        $svc->update(['deadline_days' => $val]);

        $p = Project::find($this->editInfoId);
        if ($p) $this->ei_services = $this->buildEiServices($p);
        $this->dispatch('kb-refresh'); // kartadagi timer yangilansin
        $this->dispatch('notify', type: 'success',
            message: $val ? "Muddat: {$val} kun belgilandi" : "Muddat olib tashlandi");
    }

    // GENPLAN: belgilangan PDFlarni muqova+sertifikat bilan yig'ish sahifasini ochadi
    public function eiMerge(): void
    {
        $ids = array_values(array_filter(array_map('intval', $this->ei_genplanSel)));
        if (empty($ids)) {
            $this->dispatch('notify', type: 'error', message: "Avval kamida bitta PDF belgilang");
            return;
        }
        $url = route('genplan.merge', $this->editInfoId) . '?files=' . implode(',', $ids);
        $this->js("window.open(" . json_encode($url) . ", '_blank')");
    }

    // Ish holati (work progress) — admin/menejer/mas'ul hodim o'zgartira oladi
    public function eiSetWorkStatus(string $ws): void
    {
        if (!array_key_exists($ws, Project::workStatusOptions())) return;
        $p = Project::find($this->editInfoId);
        if (!$p) return;

        $u = auth()->user();
        $allowed = $u && ($u->canSeeAllProjects()
            || $p->services()->where('assigned_user_id', $u->id)->exists());
        if (!$allowed) {
            $this->dispatch('notify', type: 'error', message: "Ruxsat yo'q");
            return;
        }

        $p->update(['work_status' => $ws]);
        $this->ei_workStatus = $ws;
        $this->dispatch('kb-refresh');
        $this->dispatch('notify', type: 'success', message: "Ish holati yangilandi");
    }

    // ── Zudlik bilan (olov) — faqat admin/menejer yoqadi/o'chiradi ──
    public function eiToggleUrgent(): void
    {
        $u = auth()->user();
        if (!$u || !$u->canSeeAllProjects()) {
            $this->dispatch('notify', type: 'error', message: "Ruxsat yo'q");
            return;
        }
        $p = Project::find($this->editInfoId);
        if (!$p) return;

        $on = !$p->is_urgent;
        $p->update([
            'is_urgent'          => $on,
            // qayta yoqilganda oldingi "qabul" tozalanadi
            'urgent_accepted_at' => $on ? null : $p->urgent_accepted_at,
            'urgent_accepted_by' => $on ? null : $p->urgent_accepted_by,
        ]);
        $this->ei_isUrgent = $on;
        $this->dispatch('kb-refresh');
        $this->dispatch('notify', type: 'success', message: $on ? "🔥 Zudlik yoqildi" : "Zudlik o'chirildi");
    }

    // Zudlikni "Qabul qildim" — faqat biriktirilgan hodim yoki admin/menejer
    public function eiAcceptUrgent(): void
    {
        $p = Project::find($this->editInfoId);
        if (!$p || !$p->is_urgent) return;

        $u = auth()->user();
        $ok = $u && ($u->canSeeAllProjects()
            || $p->services()->where('assigned_user_id', $u->id)->exists());
        if (!$ok) {
            $this->dispatch('notify', type: 'error', message: "Ruxsat yo'q");
            return;
        }

        $p->update([
            'is_urgent'          => false,
            'urgent_accepted_at' => now(),
            'urgent_accepted_by' => $u->id,
        ]);
        $this->ei_isUrgent = false;
        $this->dispatch('kb-refresh');
        $this->dispatch('notify', type: 'success', message: "Qabul qilindi — zudlik o'chdi");
    }

    // ── MyGOV (my.gov.uz) login/parol — admin/menejer/mas'ul hodim ──
    private function canMygov(?Project $p = null): bool
    {
        $p = $p ?: Project::find($this->editInfoId);
        $u = auth()->user();
        if (!$p || !$u) return false;
        return $u->canSeeAllProjects() || $p->services()->where('assigned_user_id', $u->id)->exists();
    }

    public function eiToggleMygov(): void
    {
        if (!$this->canMygov()) return;
        $this->ei_showMygov = !$this->ei_showMygov;
    }

    public function eiSaveMygov(): void
    {
        $p = Project::find($this->editInfoId);
        if (!$p || !$this->canMygov($p)) {
            $this->dispatch('notify', type: 'error', message: "Ruxsat yo'q");
            return;
        }
        $p->update([
            'mygov_login'    => trim($this->ei_mygovLogin) ?: null,
            'mygov_password' => $this->ei_mygovPassword !== '' ? $this->ei_mygovPassword : null,
            'mygov_fish'     => trim($this->ei_mygovFish) ?: null,
        ]);
        $this->dispatch('notify', type: 'success', message: "MyGOV login/parol saqlandi");
    }

    // ── "Loyiha tayyor" SMS — egasining 1-telefoniga Eskiz orqali yuboradi ──
    public function eiSendReadySms(): void
    {
        $u = auth()->user();
        if (!$u || !$u->canSeeAllProjects()) {
            $this->dispatch('notify', type: 'error', message: "Ruxsat yo'q");
            return;
        }

        $phone = trim($this->ei_phones[0] ?? '');
        if (\App\Services\EskizSms::normalizePhone($phone) === '' || strlen(\App\Services\EskizSms::normalizePhone($phone)) < 12) {
            $this->dispatch('notify', type: 'error', message: "Egasining telefon raqami noto'g'ri yoki yo'q");
            return;
        }

        $text   = config('services.eskiz.ready_message');
        $result = \App\Services\EskizSms::send($phone, $text);

        $this->dispatch('notify',
            type: $result['ok'] ? 'success' : 'error',
            message: $result['ok'] ? "📱 SMS yuborildi: {$phone}" : $result['message']
        );
    }

    public function eiAddPhone(): void { $this->ei_phones[] = '+998'; }

    public function eiRemovePhone(int $i): void
    {
        unset($this->ei_phones[$i]);
        $this->ei_phones = array_values($this->ei_phones);
        if (empty($this->ei_phones)) $this->ei_phones = ['+998'];
    }

    public function saveEditInfo(): void
    {
        $this->validate([
            'ei_owner'   => 'required|min:2',
            'ei_address' => 'required|min:3',
        ], [
            'ei_owner.required'   => 'Egasining ismi shart',
            'ei_owner.min'        => 'Ism juda qisqa',
            'ei_address.required' => 'Manzil shart',
        ]);

        $p = Project::find($this->editInfoId);
        if (!$p) { $this->closeEditInfoModal(); return; }

        $phones = array_values(array_filter(
            array_map(fn($x) => ['phone' => trim($x)], $this->ei_phones),
            fn($x) => strlen($x['phone']) > 4
        ));

        $lat = null; $lng = null;
        if (trim($this->ei_coords) !== '') {
            $parts = preg_split('/[,\s]+/', trim($this->ei_coords));
            if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $lat = (float) $parts[0];
                $lng = (float) $parts[1];
            }
        }

        $p->update([
            'owner_name'       => trim($this->ei_owner),
            'title'            => trim($this->ei_title) ?: null,
            'address'          => trim($this->ei_address),
            'oblozhka_address' => trim($this->ei_oblozhka) ?: null,
            'latitude'         => $lat,
            'longitude'        => $lng,
            'phones'           => $phones ?: null,
            'description'      => trim($this->ei_description) ?: null,
            'category'         => $this->ei_category,
            'passport_series'    => trim($this->ei_passportSeries) ?: null,
            'passport_issued_by' => trim($this->ei_passportIssuedBy) ?: null,
            'pinfl'              => trim($this->ei_pinfl) ?: null,
        ]);

        $this->closeEditInfoModal();
        $this->dispatch('kb-refresh'); // doska yangilansin (ism/manzil o'zgardi)
        $this->dispatch('notify', type: 'success', message: "Loyiha ma'lumotlari yangilandi!");
    }

    public function eiAddService(): void
    {
        $this->validate([
            'ei_newSvcType'  => 'required',
            'ei_newSvcPrice' => 'required|numeric|min:1',
        ], [
            'ei_newSvcType.required'  => 'Xizmat turini tanlang',
            'ei_newSvcPrice.required' => 'Narx kiriting',
            'ei_newSvcPrice.numeric'  => 'Narx raqam bo\'lishi kerak',
            'ei_newSvcPrice.min'      => 'Narx 0 dan katta bo\'lishi kerak',
        ]);

        $p = Project::find($this->editInfoId);
        if (!$p) return;

        if ($p->services()->where('service_name', $this->ei_newSvcType)->exists()) {
            $this->addError('ei_newSvcType', 'Bu xizmat allaqachon mavjud');
            return;
        }

        $price = (float) $this->ei_newSvcPrice;
        ProjectService::create([
            'project_id'       => $p->id,
            'assigned_user_id' => $this->ei_newSvcUser ?: null,
            'service_name'     => $this->ei_newSvcType,
            'price'            => $price,
            'discount_type'    => 'none',
            'discount_value'   => 0,
            'final_price'      => $price,
        ]);

        $this->ei_newSvcType  = '';
        $this->ei_newSvcPrice = '';
        $this->ei_newSvcUser  = null;
        $this->ei_services    = $this->buildEiServices($p->fresh());
        $this->dispatch('kb-refresh');
        $this->dispatch('notify', type: 'success', message: 'Xizmat qo\'shildi!');
    }

    public function updatedEiNewFiles(): void
    {
        $this->storeUploads($this->ei_newFiles, 'hujjat');
        $this->ei_newFiles = [];
        $this->ei_files = $this->buildEiFiles(Project::find($this->editInfoId), 'hujjat');
    }

    public function updatedEiNewGenplan(): void
    {
        $this->storeUploads($this->ei_newGenplan, 'genplan');
        $this->ei_newGenplan = [];
        $this->ei_genplan = $this->buildEiFiles(Project::find($this->editInfoId), 'genplan');
    }

    private function storeUploads($files, string $category): void
    {
        $p = Project::find($this->editInfoId);
        if (!$p) return;

        $allowedMimes = ['application/pdf','image/jpeg','image/png','image/gif','image/webp',
            'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        // Kengaytma bo'yicha ruxsat — DWG/DXF kabi fayllar mime-type'i ko'pincha
        // octet-stream yoki bo'sh bo'ladi, shu sababli faqat mime'ga ishonib bo'lmaydi.
        $allowedExts = ['pdf','jpg','jpeg','png','gif','webp','doc','docx','xls','xlsx',
            'dwg','dxf','dwf','rvt','skp','zip','rar'];

        $count = 0;
        $rejected = 0;
        foreach ((array) $files as $file) {
            if (!$file) continue;
            if ($file->getSize() > 100 * 1024 * 1024) { $rejected++; continue; }
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowedExts) && !in_array($file->getMimeType(), $allowedMimes)) { $rejected++; continue; }
            $path = $file->store('project-files/' . $p->id, 'public');
            ProjectFile::create([
                'project_id'  => $p->id,
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => $path,
                'file_type'   => $file->getMimeType(),
                'file_size'   => $file->getSize(),
                'category'    => $category,
                'uploaded_by' => auth()->id(),
            ]);
            $count++;
        }

        if ($count > 0) {
            $this->dispatch('notify', type: 'success', message: $count . " ta fayl yuklandi!");
        }
        if ($rejected > 0) {
            $this->dispatch('notify', type: 'error', message: $rejected . " ta fayl rad etildi (turi yoki hajmi 100 MB dan katta)");
        }
    }

    public function eiDeleteFile(int $fileId): void
    {
        $f = ProjectFile::find($fileId);
        if ($f && $f->project_id === $this->editInfoId) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($f->file_path);
            $f->delete();
            $p = Project::find($this->editInfoId);
            $this->ei_files   = $this->buildEiFiles($p, 'hujjat');
            $this->ei_genplan = $this->buildEiFiles($p, 'genplan');
            $this->dispatch('notify', type: 'success', message: 'Fayl o\'chirildi');
        }
    }

    private function buildEiServices(Project $p): array
    {
        $p->loadMissing(['services.assignedUser', 'payments']);
        $priceMap = [];
        foreach ($p->services as $s) $priceMap[$s->service_name] = (float) $s->final_price;

        $paid = [];
        foreach ($p->payments as $pay) {
            $svcs = $pay->services ?? [];
            if (empty($svcs)) continue;
            $sumSel = 0;
            foreach ($svcs as $sn) $sumSel += ($priceMap[$sn] ?? 0);
            foreach ($svcs as $sn) {
                $sp    = $priceMap[$sn] ?? 0;
                $share = $sumSel > 0 ? (float) $pay->amount * ($sp / $sumSel) : (float) $pay->amount / count($svcs);
                $paid[$sn] = ($paid[$sn] ?? 0) + $share;
            }
        }

        return $p->services->map(function ($s) use ($paid) {
            $price = (float) $s->final_price;
            $pd    = $paid[$s->service_name] ?? 0;
            return [
                'id'        => $s->id,
                'key'       => $s->service_name,
                'label'     => Project::serviceOptions()[$s->service_name] ?? $s->service_name,
                'price'     => $price,
                'paid'      => $pd,
                'pct'       => $price > 0 ? min(100, (int) round($pd / $price * 100)) : 0,
                'employee'  => $s->assignedUser?->name,
                'assigned_user_id' => $s->assigned_user_id,
                'completed' => (bool) $s->completed_at,
                'deadline_days' => $s->deadline_days,
                'days_left'     => $s->days_left,     // null = boshlanmagan yoki muddat yo'q
                'is_late'       => $s->is_late,
                'late_days'     => $s->late_days,
                'started'       => (bool) $s->work_started_at,
                'deadline_date' => $s->deadline_date?->translatedFormat('d-M'),
            ];
        })->toArray();
    }

    private function buildEiFiles(Project $p, string $mode = 'hujjat'): array
    {
        $query = $p->files()->orderByDesc('created_at');
        if ($mode === 'genplan') {
            $query->where('category', 'genplan');
        } else {
            // Hujjatlar — GENPLAN dan tashqari hammasi
            $query->where(function ($q) {
                $q->where('category', '!=', 'genplan')->orWhereNull('category');
            });
        }
        return $query->get()->map(function ($f) {
            $ext  = strtolower(pathinfo($f->file_name, PATHINFO_EXTENSION));
            $icon = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? '🖼️'
                  : ($ext === 'pdf' ? '📄'
                  : (in_array($ext, ['doc','docx']) ? '📝'
                  : (in_array($ext, ['xls','xlsx']) ? '📊' : '📎')));
            return [
                'id'   => $f->id,
                'name' => $f->file_name,
                'size' => $f->file_size ? round($f->file_size / 1024) . ' KB' : '',
                'icon' => $icon,
                'url'  => asset('storage/' . $f->file_path),
            ];
        })->toArray();
    }

    public function render()
    {
        $statuses = ProjectStatus::allOrdered()
            ->mapWithKeys(fn($s) => [$s->key => ['label' => $s->label, 'color' => $s->color]])
            ->toArray();

        $users = User::whereIn('role', ['admin', 'menejer', 'bajaruvchi'])
            ->orderBy('name')->get();

        $canMygov = $this->editInfoId ? $this->canMygov() : false;

        // FISH avtomat-taklif ro'yxati (avval kiritilgan ismlar)
        $mygovFishList = $canMygov
            ? Project::whereNotNull('mygov_fish')->where('mygov_fish', '!=', '')
                ->distinct()->orderBy('mygov_fish')->pluck('mygov_fish')->toArray()
            : [];

        // Oxirgi "Qabul qildim" ma'lumoti (kim/qachon) + hozir qabul qila oladimi
        $urgentAccepted   = null;
        $canAcceptUrgent  = false;
        if ($this->editInfoId) {
            $p = Project::find($this->editInfoId);
            if ($p && $p->urgent_accepted_at) {
                $urgentAccepted = [
                    'name' => User::find($p->urgent_accepted_by)?->name ?? 'Hodim',
                    'at'   => $p->urgent_accepted_at,
                ];
            }
            if ($p && $p->is_urgent) {
                $u = auth()->user();
                $canAcceptUrgent = $u && ($u->canSeeAllProjects()
                    || $p->services()->where('assigned_user_id', $u->id)->exists());
            }
        }

        return view('livewire.project-edit-modal', compact('statuses', 'users', 'canMygov', 'urgentAccepted', 'canAcceptUrgent', 'mygovFishList'));
    }
}
