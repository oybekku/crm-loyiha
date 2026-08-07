<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * "So'nggi to'lovlar" — barcha loyihalardagi to'lovlarni bitta joyda,
 * eng yangisidan boshlab ko'rsatadi (300+ loyihani birma-bir ochmasdan,
 * "bugun qaysi ishga pul tushdi" kabi savollarga tez javob topish uchun).
 * Faqat ko'rish uchun — bu yerdan to'lov qo'shib/tahrirlab bo'lmaydi
 * (u Kanban board'dagi to'lov oynasi orqali qilinadi).
 */
class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = "So'nggi to'lovlar";
    protected static ?string $modelLabel = "To'lov";
    protected static ?string $pluralModelLabel = "To'lovlar";
    protected static ?string $navigationGroup = 'Loyihalar';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['project', 'createdBy', 'account']))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kiritilgan vaqt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->description(fn (Payment $record) => "to'lov sanasi: " . ($record->payment_date?->format('d.m.Y') ?? '—')),

                Tables\Columns\TextColumn::make('project.number')
                    ->label('Loyiha')
                    ->searchable()
                    ->description(fn (Payment $record) => $record->project?->owner_name ?? '—'),

                Tables\Columns\TextColumn::make('project.created_at')
                    ->label('Tegishli oy')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state->translatedFormat('F Y')) : '—')
                    ->description('loyiha ochilgan oy')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('project.address')
                    ->label('Manzil')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Summa')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . " so'm")
                    ->weight('bold')
                    ->color('success')
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Jami')
                            ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . " so'm")
                    ),

                Tables\Columns\BadgeColumn::make('method')
                    ->label('Usul')
                    ->formatStateUsing(fn ($state) => Payment::methodOptions()[$state] ?? $state)
                    ->colors([
                        'success' => 'naqd',
                        'info'    => 'bank',
                        'warning' => 'karta',
                    ]),

                Tables\Columns\TextColumn::make('account.name')
                    ->label('Hisob')
                    ->formatStateUsing(fn ($state, Payment $record) => $state ?: ($record->account?->type ? (\App\Models\FinancialAccount::typeOptions()[$record->account->type] ?? $record->account->type) : null))
                    ->color(fn (Payment $record) => $record->account_id ? null : 'danger')
                    ->placeholder("⚠ Bog'lanmagan"),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Kim kiritdi')
                    ->default('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('note')
                    ->label('Izoh')
                    ->limit(25)
                    ->toggleable()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultGroup(
                Tables\Grouping\Group::make('created_at')
                    ->label('Oy')
                    ->getKeyFromRecordUsing(fn (Payment $record) => $record->created_at?->format('Y-m'))
                    ->getTitleFromRecordUsing(fn (Payment $record) => $record->created_at
                        ? ucfirst($record->created_at->translatedFormat('F Y'))
                        : '—')
                    ->scopeQueryByKeyUsing(fn (Builder $query, string $key) => $query
                        ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$key]))
                    ->collapsible()
            )
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Oy (kiritilgan)')
                    ->getKeyFromRecordUsing(fn (Payment $record) => $record->created_at?->format('Y-m'))
                    ->getTitleFromRecordUsing(fn (Payment $record) => $record->created_at
                        ? ucfirst($record->created_at->translatedFormat('F Y'))
                        : '—')
                    ->scopeQueryByKeyUsing(fn (Builder $query, string $key) => $query
                        ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$key]))
                    ->collapsible(),

                Tables\Grouping\Group::make('project.created_at')
                    ->label('Tegishli oy (loyiha)')
                    ->getKeyFromRecordUsing(fn (Payment $record) => $record->project?->created_at?->format('Y-m') ?? '—')
                    ->getTitleFromRecordUsing(fn (Payment $record) => $record->project?->created_at
                        ? ucfirst($record->project->created_at->translatedFormat('F Y'))
                        : "Noma'lum (loyiha o'chirilgan/arxivda)")
                    ->scopeQueryByKeyUsing(fn (Builder $query, string $key) => $key === '—'
                        ? $query->whereDoesntHave('project')
                        : $query->whereHas('project', fn ($q) => $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$key])))
                    ->collapsible(),

                Tables\Grouping\Group::make('project.owner_name')
                    ->label('Mijoz')
                    ->getKeyFromRecordUsing(fn (Payment $record) => $record->project?->owner_name ?? '—')
                    ->getTitleFromRecordUsing(fn (Payment $record) => $record->project?->owner_name ?? "Noma'lum mijoz")
                    ->scopeQueryByKeyUsing(fn (Builder $query, string $key) => $key === '—'
                        ? $query->whereDoesntHave('project')
                        : $query->whereHas('project', fn ($q) => $q->where('owner_name', $key)))
                    ->collapsible(),
            ])
            ->filters([
                Filter::make('today')
                    ->label('Faqat bugun')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today()))
                    ->toggle(),

                Filter::make('unlinked')
                    ->label("Bog'lanmagan to'lovlar")
                    ->query(fn (Builder $query) => $query->whereNull('account_id'))
                    ->toggle()
                    ->indicateUsing(fn (): string => "Hisobga bog'lanmagan to'lovlar"),

                Filter::make('created_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Kimdan')->native(false)->displayFormat('d.m.Y'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Kimgacha')->native(false)->displayFormat('d.m.Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                Filter::make('project_month')
                    ->label('Tegishli oy (loyiha)')
                    ->form([
                        \Filament\Forms\Components\Select::make('month')
                            ->label('Oy')
                            ->options(fn () => \App\Models\Project::query()
                                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym")
                                ->distinct()
                                ->orderByDesc('ym')
                                ->pluck('ym', 'ym')
                                ->mapWithKeys(fn ($ym) => [
                                    $ym => ucfirst(\Illuminate\Support\Carbon::createFromFormat('Y-m-d', "{$ym}-01")->translatedFormat('F Y')),
                                ]))
                            ->native(false)
                            ->placeholder('Barchasi'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['month'] ?? null, fn ($q, $ym) => $q->whereHas(
                            'project',
                            fn ($pq) => $pq->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$ym])
                        )))
                    ->indicateUsing(fn (array $data): ?string => ($data['month'] ?? null)
                        ? 'Tegishli oy: ' . ucfirst(\Illuminate\Support\Carbon::createFromFormat('Y-m-d', "{$data['month']}-01")->translatedFormat('F Y'))
                        : null),
            ])
            ->actions([
                Tables\Actions\Action::make('open_project')
                    ->label('Loyihaga o\'tish')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Payment $record) => $record->project
                        ? \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $record->project])
                        : null)
                    ->visible(fn (Payment $record) => (bool) $record->project)
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
