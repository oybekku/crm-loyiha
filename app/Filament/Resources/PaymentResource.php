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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['project', 'createdBy']))
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

                Tables\Columns\TextColumn::make('project.address')
                    ->label('Manzil')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Summa')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . " so'm")
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('method')
                    ->label('Usul')
                    ->formatStateUsing(fn ($state) => Payment::methodOptions()[$state] ?? $state)
                    ->colors([
                        'success' => 'naqd',
                        'info'    => 'bank',
                        'warning' => 'karta',
                    ]),

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
            ->filters([
                Filter::make('today')
                    ->label('Faqat bugun')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today()))
                    ->toggle(),

                Filter::make('created_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Kimdan'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Kimgacha'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
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
