<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\Payment;
use App\Models\Project;
use App\Models\EmployeeSalaryPayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Butun CRM ma'lumotini oddiy odam o'qiy oladigan bitta Excel faylga chiqaradi
 * (har kuni kechasi Google Drive'ga Google Sheets bo'lib yuklanadi).
 *
 * FAQAT O'QIYDI — bazada hech narsa o'zgartirmaydi.
 *
 * Xavfsizlik: oldingi eksportdagi qator sonlari storage/app/jadval/oxirgi-sonlar.json
 * da saqlanadi. Loyiha yoki to'lovlar soni 0 bo'lsa yoki keskin kamaysa
 * (>10%), fayl nomiga "SHUBHALI" qo'shiladi va buyruq xato kodi (2) bilan
 * tugaydi — yuklovchi skript Telegram'ga ogohlantirish yuboradi. Eski kunlik
 * fayllar hech qachon ustidan yozilmaydi.
 *
 * Parollar, 2FA, tokenlar, MyGov login/parol eksportga KIRMAYDI.
 */
class ExportJadval extends Command
{
    protected $signature = 'jadval:export {--nom=Makonn : Fayl nomidagi sayt nomi} {--papka= : Chiqish papkasi (standart storage/app/jadval)}';

    protected $description = "Barcha ma'lumotni o'qish uchun qulay Excel faylga eksport qiladi (Google Sheets uchun)";

    private const MONEY = '#,##0';
    private const DATE  = 'dd.mm.yyyy';
    private const DT    = 'dd.mm.yyyy hh:mm';

    private array $users = [];
    private array $accounts = [];
    private array $projects = [];   // id => [number, owner]
    private array $statuses = [];

    public function handle(): int
    {
        $now  = now();
        $dir  = $this->option('papka') ?: storage_path('app/jadval');
        @mkdir($dir, 0775, true);

        $this->users    = DB::table('users')->pluck('name', 'id')->all();
        $this->accounts = DB::table('financial_accounts')->pluck('name', 'id')->all();
        $this->statuses = DB::table('project_statuses')->pluck('label', 'key')->all() + Project::statusOptions();
        foreach (DB::table('projects')->get(['id', 'number', 'owner_name']) as $p) {
            $this->projects[$p->id] = [$p->number, $p->owner_name];
        }

        $book = new Spreadsheet();
        $book->getProperties()->setCreator('CRM')->setTitle($this->option('nom') . ' ' . $now->format('Y-m-d'));
        $book->removeSheetByIndex(0);

        $counts = [];
        $counts['loyihalar']  = $this->sheetProjects($book);
        $counts['tolovlar']   = $this->sheetPayments($book);
        $counts['ishlar']     = $this->sheetServices($book);
        $counts['maoshlar']   = $this->sheetSalaries($book);
        $counts['xarajatlar'] = $this->sheetExpenses($book);
        $counts['otkazmalar'] = $this->sheetTransfers($book);
        $monthly              = $this->sheetMonthly($book);
        $counts['xodimlar']   = $this->sheetUsers($book);
        $this->sheetAccounts($book);

        // --- Xavfsizlik tekshiruvi -------------------------------------------
        $stateFile = $dir . '/oxirgi-sonlar.json';
        $prev      = is_file($stateFile) ? (json_decode(file_get_contents($stateFile), true) ?: []) : [];
        $warnings  = [];
        foreach (['loyihalar' => 'Loyihalar', 'tolovlar' => "To'lovlar", 'ishlar' => 'Ishlar'] as $k => $label) {
            $old = (int) ($prev[$k] ?? 0);
            $new = (int) $counts[$k];
            if ($new === 0) {
                $warnings[] = "{$label}: 0 ta qator!";
            } elseif ($old > 0 && $new < $old * 0.9) {
                $warnings[] = "{$label}: {$old} → {$new} (keskin kamaydi)";
            }
        }

        $this->sheetSummary($book, $now, $counts, $monthly, $warnings);
        $book->setActiveSheetIndex(0);

        $name = $this->option('nom') . ' ' . $now->format('Y-m-d') . ($warnings ? ' SHUBHALI' : '') . '.xlsx';
        $path = $dir . '/' . $name;
        (new Xlsx($book))->save($path);

        if (! $warnings) {
            file_put_contents($stateFile, json_encode($counts + ['sana' => $now->toDateTimeString()], JSON_PRETTY_PRINT));
        }

        $this->line($path);
        foreach ($counts as $k => $v) {
            $this->line("  {$k}: {$v}");
        }
        if ($warnings) {
            foreach ($warnings as $w) {
                $this->error('  ⚠ ' . $w);
            }
            return 2;
        }
        return self::SUCCESS;
    }

    // =====================================================================
    // Varaqlar
    // =====================================================================

    private function sheetSummary(Spreadsheet $book, Carbon $now, array $c, array $monthly, array $warnings): void
    {
        $s = new Worksheet($book, 'Bosh sahifa');
        $book->addSheet($s, 0);

        $s->setCellValue('A1', $this->option('nom') . " — barcha ma'lumotlar");
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $s->setCellValue('A2', 'Oxirgi yangilanish: ' . $now->format('d.m.Y H:i'));
        $s->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('555555');

        $r = 4;
        if ($warnings) {
            $s->setCellValue("A{$r}", "⚠ DIQQAT: bu fayl shubhali — ma'lumot to'liq bo'lmasligi mumkin. Oldingi kun faylidan foydalaning!");
            $s->getStyle("A{$r}")->getFont()->setBold(true)->getColor()->setRGB('C00000');
            $r++;
            foreach ($warnings as $w) {
                $s->setCellValue("A{$r}", '   • ' . $w);
                $s->getStyle("A{$r}")->getFont()->getColor()->setRGB('C00000');
                $r++;
            }
            $r++;
        }

        // Shu oy
        $ym  = $now->format('Y-m');
        $cur = $monthly[$ym] ?? ['kirim' => 0, 'xarajat' => 0, 'oylik' => 0];
        $s->setCellValue("A{$r}", 'Shu oy (' . $this->monthLabel($ym) . ')');
        $s->getStyle("A{$r}")->getFont()->setBold(true)->setSize(13);
        $r++;
        foreach ([
            ['Kirim (mijozlardan tushgan)', $cur['kirim']],
            ['Maosh va avanslar', $cur['oylik']],
            ['Boshqa xarajatlar', $cur['xarajat']],
            ['Qoldiq (kirim − chiqim)', $cur['kirim'] - $cur['oylik'] - $cur['xarajat']],
        ] as [$l, $v]) {
            $s->setCellValue("A{$r}", $l);
            $s->setCellValue("B{$r}", $v);
            $s->getStyle("B{$r}")->getNumberFormat()->setFormatCode(self::MONEY);
            $r++;
        }
        $s->getStyle('A' . ($r - 1) . ':B' . ($r - 1))->getFont()->setBold(true);
        $r++;

        // Mundarija
        $s->setCellValue("A{$r}", 'Varaqlar (pastdagi yorliqlardan oching)');
        $s->getStyle("A{$r}")->getFont()->setBold(true)->setSize(13);
        $r++;
        $s->setCellValue("A{$r}", 'Varaq');
        $s->setCellValue("B{$r}", 'Soni');
        $s->setCellValue("C{$r}", 'Ichida nima bor');
        $this->headerStyle($s, "A{$r}:C{$r}");
        $r++;
        foreach ([
            ['Loyihalar', $c['loyihalar'], "Har bir loyiha bitta qatorda: mijoz, telefon, manzil, summa, to'langan, qoldiq, holat"],
            ["To'lovlar", $c['tolovlar'], "Mijozlardan tushgan har bir to'lov"],
            ['Ishlar', $c['ishlar'], "Loyihadagi har bir xizmat: kim bajaradi, narxi, qachon topshirildi"],
            ['Maoshlar', $c['maoshlar'], 'Xodimlarga berilgan maosh va avanslar'],
            ['Xarajatlar', $c['xarajatlar'], 'Barcha chiqimlar (maosh ham shu yerda ko\'rinadi)'],
            ["Pul o'tkazmalari", $c['otkazmalar'], "Hisoblar (kassa/karta) orasida o'tkazilgan pullar"],
            ['Oylik hisobot', count($monthly), "Har oy: kirim, maosh, xarajat, qoldiq"],
            ['Xodimlar', $c['xodimlar'], 'Xodimlar ro\'yxati: lavozim, telefon, oklad'],
            ['Hisoblar', count($this->accounts), 'Kassa, karta, bank hisoblari va ulardagi qoldiq'],
        ] as [$n, $cnt, $d]) {
            $s->setCellValue("A{$r}", $n);
            $s->setCellValue("B{$r}", $cnt);
            $s->setCellValue("C{$r}", $d);
            $r++;
        }
        $r++;
        $s->setCellValue("A{$r}", "Maslahat: har bir varaqda sarlavha qatoridagi ▼ filtr tugmasi orqali qidiring (masalan, mijoz ismi yoki telefon bo'yicha). Eng yangi yozuvlar doim tepada.");
        $s->getStyle("A{$r}")->getFont()->setItalic(true)->getColor()->setRGB('555555');
        $r++;
        $s->setCellValue("A{$r}", "Bu fayl faqat o'qish uchun nusxa. Ma'lumotni o'zgartirish faqat CRM'da.");
        $s->getStyle("A{$r}")->getFont()->setItalic(true)->getColor()->setRGB('555555');

        $s->getColumnDimension('A')->setWidth(34);
        $s->getColumnDimension('B')->setWidth(16);
        $s->getColumnDimension('C')->setWidth(80);
    }

    private function sheetProjects(Spreadsheet $book): int
    {
        $services = DB::table('project_services')->orderBy('id')->get(['project_id', 'service_name', 'assigned_user_id'])
            ->groupBy('project_id');
        $srvOpt = Project::serviceOptions();

        $rows = [];
        $q = DB::table('projects')->whereNull('deleted_at')->orderByDesc('created_at')->orderByDesc('id')->get();
        foreach ($q as $p) {
            $srv = collect($services[$p->id] ?? [])->map(fn ($s) => $srvOpt[$s->service_name] ?? $s->service_name)->implode(', ');
            $workers = collect($services[$p->id] ?? [])->pluck('assigned_user_id')->filter()->unique()
                ->map(fn ($u) => $this->users[$u] ?? null)->filter()->implode(', ');
            $phones = json_decode($p->phones ?? '[]', true);
            $phones = is_array($phones) ? implode(', ', array_filter(array_map(
                fn ($x) => $this->phone(is_array($x) ? ($x['number'] ?? $x['phone'] ?? implode(' ', $x)) : $x), $phones))) : $this->phone($p->phones);
            $total = (float) $p->total_price;
            $paid  = (float) $p->paid_amount;
            $rows[] = [
                $p->number,
                $this->d($p->created_at),
                $p->owner_name,
                $phones,
                $p->address,
                trim((Project::regionOptions()[$p->region] ?? ($p->region ?? '')) . ', ' . ($p->district ?? ''), ', '),
                $p->cadastre_number,
                Project::categoryOptions()[$p->category] ?? $p->category,
                $srv,
                // Bazadagi (eskirgan) loyiha — zaxira nusxada qoladi, lekin aniq belgilanadi
                (!empty($p->baza_at) ? '📦 Baza · ' : '') . ($this->statuses[$p->status] ?? $p->status),
                $this->users[$p->assigned_user_id] ?? '',
                $workers,
                $total,
                $paid,
                $total - $paid,
                $this->d($p->deadline_date),
                $p->is_urgent ? 'Ha' : '',
                $p->is_didox ? 'Ha' : '',
                $p->debt_comment,
                $p->description,
                $p->passport_series,
                $p->pinfl,
                $this->dt($p->updated_at),
            ];
        }

        $this->addTable($book, 'Loyihalar', [
            ['№', 8], ['Sana', 11, self::DATE], ['Mijoz', 28], ['Telefon', 18], ['Manzil', 30], ['Viloyat / tuman', 18],
            ['Kadastr raqami', 18, '@'],['Turi', 11], ['Xizmatlar', 24], ['Holat', 16], ["Mas'ul", 16], ['Ijrochilar', 20],
            ['Umumiy summa', 14, self::MONEY], ["To'langan", 14, self::MONEY], ['Qoldiq (qarz)', 14, self::MONEY],
            ['Muddat', 11, self::DATE], ['Shoshilinch', 10], ['Didox', 8], ['Qarz izohi', 24], ['Izoh', 40],
            ['Pasport', 12, '@'], ['JShShIR', 16, '@'],["Oxirgi o'zgarish", 16, self::DT],
        ], $rows);

        return count($rows);
    }

    private function sheetPayments(Spreadsheet $book): int
    {
        $srvOpt = Project::serviceOptions();
        $rows = [];
        foreach (DB::table('payments')->orderByDesc('payment_date')->orderByDesc('id')->get() as $p) {
            [$num, $owner] = $this->projects[$p->project_id] ?? ['', ''];
            $split = json_decode($p->service_split ?? '', true);
            $splitTxt = is_array($split) ? collect($split)->map(fn ($v, $k) => ($srvOpt[$k] ?? $k) . ': ' . number_format((float) $v, 0, '.', ' '))->implode('; ') : '';
            $rows[] = [
                $this->d($p->payment_date),
                $num,
                $owner,
                (float) $p->amount,
                Payment::methodOptions()[$p->method] ?? $p->method,
                $this->accounts[$p->account_id] ?? '',
                $splitTxt,
                $p->note,
                $this->users[$p->created_by] ?? '',
                $this->dt($p->created_at),
            ];
        }
        $this->addTable($book, "To'lovlar", [
            ['Sana', 11, self::DATE], ['Loyiha №', 9], ['Mijoz', 28], ['Summa', 14, self::MONEY], ['Usul', 13],
            ['Qaysi hisobga', 18], ["Xizmatlar bo'yicha", 30], ['Izoh', 36], ['Kiritgan', 16], ['Kiritilgan vaqti', 16, self::DT],
        ], $rows, sumCol: 'D');
        return count($rows);
    }

    private function sheetServices(Spreadsheet $book): int
    {
        $srvOpt = Project::serviceOptions();
        $rows = [];
        $q = DB::table('project_services')->orderByDesc('project_id')->orderBy('id')->get();
        foreach ($q as $s) {
            [$num, $owner] = $this->projects[$s->project_id] ?? ['', ''];
            $status = $s->completed_at ? 'Tugallangan' : ($s->submitted_at ? 'Topshirilgan' : ($s->work_started_at ? 'Jarayonda' : 'Boshlanmagan'));
            $disc = (float) $s->discount_value > 0
                ? number_format((float) $s->discount_value, 0, '.', ' ') . ($s->discount_type === 'percent' ? ' %' : '')
                : '';
            $rows[] = [
                $num,
                $owner,
                $srvOpt[$s->service_name] ?? $s->service_name,
                $this->users[$s->assigned_user_id] ?? '',
                $s->work_month ? $this->monthLabel(substr($s->work_month, 0, 7)) : '',
                (float) $s->price,
                $disc,
                (float) $s->final_price,
                $status,
                $this->d($s->work_started_at),
                $this->d($s->submitted_at),
                $this->d($s->completed_at),
                $s->note,
            ];
        }
        $this->addTable($book, 'Ishlar', [
            ['Loyiha №', 9], ['Mijoz', 28], ['Xizmat', 16], ['Ijrochi', 18], ['Ish oyi', 14], ['Narx', 13, self::MONEY],
            ['Chegirma', 10], ['Yakuniy narx', 13, self::MONEY], ['Holat', 13], ['Boshlangan', 11, self::DATE],
            ['Topshirilgan', 11, self::DATE], ['Tugallangan', 11, self::DATE], ['Izoh', 36],
        ], $rows, sumCol: 'H');
        return count($rows);
    }

    private function sheetSalaries(Spreadsheet $book): int
    {
        $types = EmployeeSalaryPayment::typeOptions();
        $rows = [];
        foreach (DB::table('employee_salary_payments')->get() as $p) {
            $rows[] = [
                $this->d($p->paid_at), $this->users[$p->user_id] ?? '', $types[$p->salary_type] ?? ($p->salary_type ?: 'Maosh'),
                $this->monthLabel($p->month), $p->wallet_month && $p->wallet_month !== $p->month ? $this->monthLabel($p->wallet_month) : '',
                (float) $p->amount, $this->users[$p->given_by] ?? '', $p->note, $p->paid_at,
            ];
        }
        foreach (DB::table('employee_advances')->get() as $a) {
            $rows[] = [
                $this->d($a->given_at), $this->users[$a->user_id] ?? '', 'Avans',
                $this->monthLabel($a->month), '', (float) $a->amount, $this->users[$a->given_by] ?? '', $a->note, $a->given_at,
            ];
        }
        usort($rows, fn ($a, $b) => strcmp((string) $b[8], (string) $a[8]));
        $rows = array_map(fn ($r) => array_slice($r, 0, 8), $rows);

        $this->addTable($book, 'Maoshlar', [
            ['Berilgan sana', 12, self::DATE], ['Xodim', 20], ['Turi', 24], ['Qaysi oy uchun', 15],
            ['Pul qaysi oy hamyonidan', 18], ['Summa', 14, self::MONEY], ['Bergan', 16], ['Izoh', 36],
        ], $rows, sumCol: 'F');
        return count($rows);
    }

    private function sheetExpenses(Spreadsheet $book): int
    {
        $recurring = DB::table('recurring_expenses')->pluck('name', 'id')->all();
        $rows = [];
        foreach (DB::table('expenses')->orderByDesc('expense_date')->orderByDesc('id')->get() as $e) {
            $kind = $e->category ?: ($e->user_id ? Expense::KIND_OYLIK : Expense::KIND_XARAJAT);
            [$num] = $this->projects[$e->project_id] ?? [''];
            $rows[] = [
                $this->d($e->expense_date),
                Expense::kindOptions()[$kind] ?? $kind,
                $recurring[$e->recurring_expense_id] ?? '',
                $this->users[$e->user_id] ?? ($this->users[$e->responsible_id] ?? ''),
                $num,
                $e->month ? $this->monthLabel($e->month) : '',
                (float) $e->amount,
                $this->accounts[$e->account_id] ?? '',
                trim(($e->comment ?? '') . ($e->note ? ' — ' . $e->note : ''), ' —'),
                $this->users[$e->created_by] ?? '',
            ];
        }
        $this->addTable($book, 'Xarajatlar', [
            ['Sana', 11, self::DATE], ['Turi', 13], ['Doimiy xarajat', 18], ['Xodim', 18], ['Loyiha №', 9],
            ['Oy', 14], ['Summa', 14, self::MONEY], ['Qaysi hisobdan', 18], ['Izoh', 44], ['Kiritgan', 16],
        ], $rows, sumCol: 'G');
        return count($rows);
    }

    private function sheetTransfers(Spreadsheet $book): int
    {
        $rows = [];
        foreach (DB::table('account_transfers')->orderByDesc('transfer_date')->orderByDesc('id')->get() as $t) {
            $rows[] = [
                $this->d($t->transfer_date), $this->accounts[$t->from_account_id] ?? '', $this->accounts[$t->to_account_id] ?? '',
                (float) $t->amount,
                $t->month ? $this->monthLabel($t->month) : '', $t->to_month ? $this->monthLabel($t->to_month) : '',
                $t->comment, $this->users[$t->created_by] ?? '',
            ];
        }
        $this->addTable($book, "Pul o'tkazmalari", [
            ['Sana', 11, self::DATE], ['Qaysi hisobdan', 20], ['Qaysi hisobga', 20], ['Summa', 14, self::MONEY],
            ['Qaysi oydan', 14], ['Qaysi oyga', 14], ['Izoh', 40], ['Kiritgan', 16],
        ], $rows);
        return count($rows);
    }

    private function sheetMonthly(Spreadsheet $book): array
    {
        $m = [];
        foreach (DB::table('payments')->selectRaw("DATE_FORMAT(payment_date,'%Y-%m') ym, SUM(amount) s")->groupBy('ym')->get() as $r) {
            $m[$r->ym]['kirim'] = (float) $r->s;
        }
        foreach (DB::table('expenses')->selectRaw("DATE_FORMAT(expense_date,'%Y-%m') ym, COALESCE(category, IF(user_id IS NULL,'xarajat','oylik')) k, SUM(amount) s")->groupBy('ym', 'k')->get() as $r) {
            $m[$r->ym][$r->k === 'oylik' ? 'oylik' : 'xarajat'] = ($m[$r->ym][$r->k === 'oylik' ? 'oylik' : 'xarajat'] ?? 0) + (float) $r->s;
        }
        foreach (DB::table('projects')->whereNull('deleted_at')->selectRaw("DATE_FORMAT(created_at,'%Y-%m') ym, COUNT(*) c")->groupBy('ym')->get() as $r) {
            $m[$r->ym]['loyiha'] = (int) $r->c;
        }
        unset($m['']);
        krsort($m);
        foreach ($m as $k => $v) {
            $m[$k] = $v + ['kirim' => 0, 'xarajat' => 0, 'oylik' => 0, 'loyiha' => 0];
        }

        $rows = [];
        foreach ($m as $ym => $v) {
            $rows[] = [$this->monthLabel($ym), $v['loyiha'], $v['kirim'], $v['oylik'], $v['xarajat'], $v['kirim'] - $v['oylik'] - $v['xarajat']];
        }
        $this->addTable($book, 'Oylik hisobot', [
            ['Oy', 16], ['Yangi loyihalar', 14], ['Kirim', 15, self::MONEY], ['Maosh va avans', 15, self::MONEY],
            ['Boshqa xarajat', 15, self::MONEY], ['Qoldiq', 15, self::MONEY],
        ], $rows);
        $book->getSheetByName('Oylik hisobot')->setCellValue('H1', "Kirim — to'lov sanasi bo'yicha, chiqim — xarajat sanasi bo'yicha. Aniq hisobot (ish oyi bo'yicha) CRM'dagi Hisobotlarda.");
        $book->getSheetByName('Oylik hisobot')->getStyle('H1')->getFont()->setItalic(true)->getColor()->setRGB('555555');
        return $m;
    }

    private function sheetUsers(Spreadsheet $book): int
    {
        $roles = ['admin' => 'Admin', 'menejer' => 'Menejer', 'hisobchi' => 'Hisobchi', 'bajaruvchi' => 'Bajaruvchi'];
        $rows = [];
        foreach (DB::table('users')->orderByDesc('is_active')->orderBy('name')->get() as $u) {
            $rows[] = [
                $u->name, $u->position, $roles[$u->role] ?? $u->role, $this->phone($u->phone), $u->email,
                $u->is_active ? 'Ha' : "Yo'q", EmployeeSalaryPayment::typeOptions()[$u->salary_type] ?? $u->salary_type,
                (float) $u->base_salary ?: '', (float) $u->commission_rate ?: '',
                $this->d($u->created_at),
            ];
        }
        $this->addTable($book, 'Xodimlar', [
            ['Ism', 24], ['Lavozim', 20], ['Rol', 12], ['Telefon', 16], ['Login (email)', 26], ['Faol', 7],
            ['Maosh turi', 22], ['Oklad', 14, self::MONEY], ['Komissiya %', 11], ["Qo'shilgan", 11, self::DATE],
        ], $rows);
        return count($rows);
    }

    private function sheetAccounts(Spreadsheet $book): void
    {
        $types = FinancialAccount::typeOptions();
        $rows = [];
        foreach (FinancialAccount::orderBy('sort_order')->get() as $a) {
            $rows[] = [$a->name, $types[$a->type] ?? $a->type, $this->users[$a->user_id] ?? '', $a->bank_name, $a->balance];
        }
        $this->addTable($book, 'Hisoblar', [
            ['Nomi', 24], ['Turi', 18], ['Egasi', 18], ['Bank', 18], ['Qoldiq (balans)', 16, self::MONEY],
        ], $rows, sumCol: 'E');
    }

    // =====================================================================
    // Yordamchilar
    // =====================================================================

    /** @param array<int, array{0:string,1:int,2?:string}> $cols */
    private function addTable(Spreadsheet $book, string $title, array $cols, array $rows, ?string $sumCol = null): void
    {
        $s = new Worksheet($book, $title);
        $book->addSheet($s);

        $s->fromArray(array_column($cols, 0), null, 'A1');
        $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cols));
        $this->headerStyle($s, "A1:{$last}1");

        if ($rows) {
            $s->fromArray($rows, null, 'A2', true);
        }
        $n = count($rows) + 1;

        foreach ($cols as $i => $c) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $s->getColumnDimension($col)->setWidth($c[1]);
            if (($c[2] ?? null) === '@') {
                // Matn ustun (kadastr, pasport, JShShIR) — son bo'lib ketmasin
                foreach ($rows as $ri => $row) {
                    $v = $row[$i] ?? '';
                    if ($v !== '' && $v !== null) {
                        $s->setCellValueExplicit($col . ($ri + 2), (string) $v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }
            } elseif (! empty($c[2]) && $n > 1) {
                $s->getStyle("{$col}2:{$col}{$n}")->getNumberFormat()->setFormatCode($c[2]);
            }
        }
        if ($n > 1) {
            $s->getStyle("A2:{$last}{$n}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            // Har ikkinchi qator och kulrang — ko'z adashmasligi uchun
            for ($r = 3; $r <= $n; $r += 2) {
                $s->getStyle("A{$r}:{$last}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5F7FA');
            }
        }

        if ($sumCol && $n > 1) {
            $t = $n + 2;
            $s->setCellValue("A{$t}", 'JAMI (filtrga qarab):');
            $s->setCellValue("{$sumCol}{$t}", "=SUBTOTAL(9,{$sumCol}2:{$sumCol}{$n})");
            $s->getStyle("{$sumCol}{$t}")->getNumberFormat()->setFormatCode(self::MONEY);
            $s->getStyle("A{$t}:{$last}{$t}")->getFont()->setBold(true);
        }

        $s->freezePane('A2');
        $s->setAutoFilter("A1:{$last}{$n}");
    }

    private function headerStyle(Worksheet $s, string $range): void
    {
        $st = $s->getStyle($range);
        $st->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $st->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E79');
        $st->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
    }

    /** Sana → Excel sanasi (Google Sheets'da haqiqiy sana bo'lib, saralanadi) */
    private function d($v)
    {
        if (! $v) {
            return '';
        }
        return XlDate::PHPToExcel(Carbon::parse($v)->startOfDay());
    }

    private function dt($v)
    {
        return $v ? XlDate::PHPToExcel(Carbon::parse($v)) : '';
    }

    /** 998901234567 → "+998 90 123 45 67" (matn, son bo'lib ketmaydi) */
    private function phone($v): string
    {
        $d = preg_replace('/\D/', '', (string) $v);
        if ($d === '' || preg_match('/^998?0+$/', $d)) {
            return '';
        }
        if (strlen($d) === 9) {
            $d = '998' . $d;
        }
        return strlen($d) === 12
            ? sprintf('+%s %s %s %s %s', substr($d, 0, 3), substr($d, 3, 2), substr($d, 5, 3), substr($d, 8, 2), substr($d, 10, 2))
            : (string) $v;
    }

    private function monthLabel(?string $ym): string
    {
        if (! $ym || ! preg_match('/^(\d{4})-(\d{2})/', $ym, $m)) {
            return (string) $ym;
        }
        $names = [1 => 'Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'];
        return $names[(int) $m[2]] . ' ' . $m[1];
    }
}
