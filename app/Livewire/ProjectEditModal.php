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
    public array  $ei_services       = [];
    public array  $ei_files          = [];
    public $ei_newFiles              = [];
    public string $ei_status         = '';
    public bool   $ei_paymentRequested = false;
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
        $this->ei_services    = $this->buildEiServices($p);
        $this->ei_files       = $this->buildEiFiles($p);
        $this->ei_newFiles    = [];
        $this->ei_status      = $p->status;
        $this->ei_paymentRequested = (bool) $p->payment_requested_at;
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
        $this->eiSaveFiles();
    }

    public function eiSaveFiles(): void
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
        foreach ((array) $this->ei_newFiles as $file) {
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
                'uploaded_by' => auth()->id(),
            ]);
            $count++;
        }

        $this->ei_newFiles = [];
        $this->ei_files = $this->buildEiFiles($p);
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
            $this->ei_files = array_values(array_filter($this->ei_files, fn($x) => $x['id'] !== $fileId));
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
                'key'      => $s->service_name,
                'label'    => Project::serviceOptions()[$s->service_name] ?? $s->service_name,
                'price'    => $price,
                'paid'     => $pd,
                'pct'      => $price > 0 ? min(100, (int) round($pd / $price * 100)) : 0,
                'employee' => $s->assignedUser?->name,
            ];
        })->toArray();
    }

    private function buildEiFiles(Project $p): array
    {
        return $p->files()->orderByDesc('created_at')->get()->map(function ($f) {
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

        return view('livewire.project-edit-modal', compact('statuses', 'users'));
    }
}
