<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';
    protected static ?string $title = 'Xizmatlar';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('service_name')
                ->label('Xizmat turi')
                ->options(Project::serviceOptions())
                ->required(),

            Forms\Components\TextInput::make('price')
                ->label("Narxi (so'm)")
                ->numeric()
                ->required()
                ->default(0)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set) {
                    self::recalculate($get, $set);
                }),

            Forms\Components\Select::make('discount_type')
                ->label('Chegirma turi')
                ->options(['none' => "Chegirmasiz", 'percent' => 'Foiz (%)', 'fixed' => "Belgilangan summa"])
                ->default('none')
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set) {
                    self::recalculate($get, $set);
                }),

            Forms\Components\TextInput::make('discount_value')
                ->label('Chegirma miqdori')
                ->numeric()
                ->default(0)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set) {
                    self::recalculate($get, $set);
                }),

            Forms\Components\TextInput::make('final_price')
                ->label("Yakuniy narx (so'm)")
                ->numeric()
                ->readOnly()
                ->default(0),

            Forms\Components\Select::make('assigned_user_id')
                ->label('Mas\'ul hodim')
                ->options(
                    User::whereIn('role', ['menejer', 'bajaruvchi', 'admin'])
                        ->orderBy('name')
                        ->pluck('name', 'id')
                )
                ->nullable()
                ->placeholder('— Tanlang —'),

            Forms\Components\Textarea::make('note')
                ->label('Izoh')
                ->rows(2)
                ->columnSpanFull(),

            self::confirmField("Narx yoki chegirma o'zgarsa (narxi bor loyihada) — kerak. Faqat mas'ul/izoh o'zgarsa — shart emas."),
        ]);
    }

    /** Google Authenticator kodi maydoni (ActionConfirmService) — saqlanmaydi, before()da tekshiriladi */
    protected static function confirmField(string $hint): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('confirm_code')
            ->label('🔐 Google Authenticator kodi')
            ->helperText(fn () => \App\Services\ActionConfirmService::canConfirm(auth()->user()) ? $hint
                : new \Illuminate\Support\HtmlString("Avval <a href=\"" . e(\App\Services\ActionConfirmService::setupUrl()) . "\" style=\"color:#4338ca;font-weight:600\">Google Authenticator'ni ulang</a>."))
            ->placeholder('······')
            ->maxLength(6)
            ->visible(fn () => \App\Services\ActionConfirmService::required())
            ->columnSpanFull();
    }

    /** Loyiha narxiga ta'sir qilsa — kodni tekshiradi; noto'g'ri bo'lsa amalni to'xtatadi */
    protected static function checkCode(bool $affectsPrice, array $data, Tables\Actions\Action|Tables\Actions\BulkAction $action): void
    {
        if (!$affectsPrice || \App\Services\ActionConfirmService::verify(auth()->user(), $data['confirm_code'] ?? '')) return;

        \Filament\Notifications\Notification::make()
            ->title(\App\Services\ActionConfirmService::canConfirm(auth()->user())
                ? (filled($data['confirm_code'] ?? null) ? "Noto'g'ri yoki eskirgan kod" : "Loyiha narxi o'zgaradi — Google Authenticator kodini kiriting")
                : "Avval Google Authenticator'ni ulang")
            ->danger()->send();
        $action->halt();
    }

    protected static function finalOf(array $data): float
    {
        $price = (float) ($data['price'] ?? 0);
        $val   = (float) ($data['discount_value'] ?? 0);
        return max(0, match ($data['discount_type'] ?? 'none') {
            'percent' => $price - ($price * $val / 100),
            'fixed'   => $price - $val,
            default   => $price,
        });
    }

    protected static function recalculate(Get $get, Set $set): void
    {
        $price = (float) $get('price');
        $type  = $get('discount_type');
        $val   = (float) $get('discount_value');
        $final = match ($type) {
            'percent' => $price - ($price * $val / 100),
            'fixed'   => max(0, $price - $val),
            default   => $price,
        };
        $set('final_price', round($final, 2));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service_name')
            ->columns([
                Tables\Columns\TextColumn::make('service_name')
                    ->label('Xizmat')
                    ->formatStateUsing(fn($state) => Project::serviceOptions()[$state] ?? $state),

                Tables\Columns\TextColumn::make('price')
                    ->label("Narx")
                    ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . " so'm"),

                Tables\Columns\TextColumn::make('discount_type')
                    ->label('Chegirma')
                    ->formatStateUsing(function ($state, $record) {
                        if ($state === 'none') return '—';
                        if ($state === 'percent') return $record->discount_value . '%';
                        return number_format($record->discount_value, 0, '.', ' ') . " so'm";
                    }),

                Tables\Columns\TextColumn::make('final_price')
                    ->label("Yakuniy narx")
                    ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . " so'm")
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Mas\'ul hodim')
                    ->default('—')
                    ->badge()
                    ->color('info'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Xizmat qo\'shish')
                    // Keyinroq qo'shilgan xizmat — qo'shilgan oyiga (ProjectService::work_month)
                    // confirm_code $fillable'da yo'q — saqlashda o'zi tushib qoladi (before() uni ko'rishi kerak)
                    ->mutateFormDataUsing(fn (array $data) => $data + ['work_month' => now()->format('Y-m')])
                    ->before(fn (array $data, Tables\Actions\CreateAction $action) => self::checkCode(
                        \App\Services\PriceChangeRecorder::createAffectsPrice($this->getOwnerRecord()->id, self::finalOf($data)), $data, $action)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('')
                    ->before(fn (array $data, $record, Tables\Actions\EditAction $action) => self::checkCode(
                        \App\Services\PriceChangeRecorder::updateAffectsPrice((float) $record->final_price, self::finalOf($data)), $data, $action)),
                Tables\Actions\DeleteAction::make()->label('')
                    ->form(fn ($record) => \App\Services\PriceChangeRecorder::deleteAffectsPrice($record)
                        ? [self::confirmField("Narxi bor xizmat olib tashlanadi — loyiha narxi kamayadi.")] : [])
                    ->before(fn (array $data, $record, Tables\Actions\DeleteAction $action) => self::checkCode(
                        \App\Services\PriceChangeRecorder::deleteAffectsPrice($record), $data, $action)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->form([self::confirmField("Narxi bor xizmatlar olib tashlansa — loyiha narxi kamayadi.")])
                        ->before(fn (array $data, $records, Tables\Actions\DeleteBulkAction $action) => self::checkCode(
                            $records->contains(fn ($r) => \App\Services\PriceChangeRecorder::deleteAffectsPrice($r)), $data, $action)),
                ]),
            ]);
    }
}
