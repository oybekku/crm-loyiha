<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Traits\HasMenuPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    use HasMenuPermission;

    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission(static::menuPermissionKey()) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() && $record->id !== auth()->id();
    }
    protected static ?string $navigationLabel = 'Xodimlar';
    protected static ?string $modelLabel = 'Xodim';
    protected static ?string $pluralModelLabel = 'Xodimlar';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Ismi')
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->label('Telefon')
                    ->tel()
                    ->placeholder('+998 XX XXX XX XX'),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages(['unique' => 'Bu email allaqachon ro\'yxatda bor.']),

                Forms\Components\Select::make('role')
                    ->label('Lavozimi')
                    ->options([
                        'admin'      => 'Admin',
                        'menejer'    => 'Menejer',
                        'hisobchi'   => 'Hisobchi',
                        'bajaruvchi' => 'Bajaruvchi',
                    ])
                    ->required()
                    ->default('menejer'),

                Forms\Components\TextInput::make('password')
                    ->label('Parol')
                    ->password()
                    ->revealable()
                    ->required(fn($operation) => $operation === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->placeholder('Yangi parol kiriting'),

                // Admin akkauntini tahrirlash uchun Google Authenticator kodi — kimdir kirib o'zgartirib qo'ymasin
                Forms\Components\TextInput::make('pin_confirm')
                    ->label('🔐 Google Authenticator kodi (admin login/parolini o\'zgartirish uchun)')
                    ->helperText(fn () => \App\Services\ActionConfirmService::canConfirm(auth()->user()) ? null
                        : new \Illuminate\Support\HtmlString("Avval <a href=\"" . e(\App\Services\ActionConfirmService::setupUrl()) . "\" style=\"color:#4338ca;font-weight:600\">Google Authenticator'ni ulang</a>."))
                    ->dehydrated(false)
                    ->visible(fn($operation, $record) => $operation === 'edit' && $record?->isAdmin() && \App\Services\ActionConfirmService::required())
                    ->required(fn($operation, $record) => $operation === 'edit' && $record?->isAdmin() && \App\Services\ActionConfirmService::required())
                    ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                        if (!\App\Services\ActionConfirmService::canConfirm(auth()->user())) {
                            $fail("Avval Google Authenticator'ni ulang");
                        } elseif (!\App\Services\ActionConfirmService::verify(auth()->user(), (string) $value)) {
                            $fail("Noto'g'ri yoki eskirgan kod");
                        }
                    })
                    ->placeholder('······'),

                Forms\Components\TextInput::make('commission_rate')
                    ->label('Komissiya foizi (%)')
                    ->numeric()
                    ->default(20)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->step(0.5)
                    ->helperText('Har bir xizmat narxidan necha % hodimga tegishli. Bu — standart foiz (tarix bo\'lmagan barcha oylar uchun). Ma\'lum bir oydan boshlab foizni o\'zgartirish uchun Oylik hisobot sahifasida hodim ismi yonidagi foizga bosing.'),
            ]),
        ]);
    }

    /** Oklad oynasi maydonlari: summa + "qaysi oydan boshlab" (oldingi oylar o'zgarmaydi) */
    protected static function okladFields(bool $withAmount = true): array
    {
        $fields = [];
        if ($withAmount) {
            $fields[] = Forms\Components\TextInput::make('amount')
                ->label("Oklad — oylik maosh (so'm)")
                ->numeric()->minValue(0)->required()->autofocus()
                ->placeholder('Masalan: 5000000')
                ->helperText("Har oy \"To'lanishi kerak\"ga qo'shiladi (ish bo'lmasa ham). \"📅 Oylik maosh (oklad)\" turidagi to'lov bilan yopiladi.");
        }
        $fields[] = Forms\Components\TextInput::make('from_month')
            ->label('Qaysi oydan boshlab')
            ->type('month')->required()
            ->default(now()->format('Y-m'))
            ->helperText("Shu oy va keyingi oylarga ta'sir qiladi. Oldingi oylar o'zgarmaydi.");
        return $fields;
    }

    /** Oklad tarixi — oyna tavsifi uchun */
    protected static function okladHistory(User $u): \Illuminate\Support\HtmlString|string
    {
        $rows = \App\Models\UserBaseSalary::where('user_id', $u->id)->orderByDesc('effective_month')->limit(6)->get();
        if ($rows->isEmpty()) return 'Hozircha oklad belgilanmagan.';
        $li = $rows->map(fn ($r) => '<li>' . e($r->effective_month === '2000-01' ? 'boshidan' : $r->effective_month . ' dan') . ': <b>'
            . ($r->amount > 0 ? number_format($r->amount, 0, '.', ' ') . " so'm" : "oklad yo'q") . '</b></li>')->implode('');
        return new \Illuminate\Support\HtmlString("<div style=\"font-size:12px\">Tarix:<ul style=\"margin:4px 0 0 16px;list-style:disc\">{$li}</ul></div>");
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Ismi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->default('—'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Login (email)')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Lavozim')
                    ->formatStateUsing(fn($state) => match($state) {
                        'admin'      => 'Admin',
                        'menejer'    => 'Menejer',
                        'hisobchi'   => 'Hisobchi',
                        'bajaruvchi' => 'Bajaruvchi',
                        default      => $state,
                    })
                    ->colors([
                        'danger'  => 'admin',
                        'primary' => 'menejer',
                        'warning' => 'hisobchi',
                        'success' => 'bajaruvchi',
                    ]),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Komissiya')
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('projects_count')
                    ->label('Loyihalar')
                    ->counts('projects')
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_salary')
                    ->label('Oklad')
                    ->formatStateUsing(fn ($state) => (float) $state > 0 ? number_format((float) $state, 0, '.', ' ') . " so'm" : '—')
                    ->color(fn ($state) => (float) $state > 0 ? 'success' : 'gray')
                    ->weight(fn ($state) => (float) $state > 0 ? 'bold' : null)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Oklad — qo'shish/tahrirlash "shu oydan boshlab" (admin akkauntida ham kodsiz: login/parolga tegmaydi)
                Tables\Actions\Action::make('oklad')
                    ->label(fn ($record) => (float) $record->base_salary > 0 ? 'Oklad' : "Oklad qo'shish")
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->isAdmin())
                    ->modalHeading(fn ($record) => "{$record->name} — oklad")
                    ->modalDescription(fn ($record) => self::okladHistory($record))
                    ->modalWidth('md')
                    ->fillForm(fn ($record) => ['amount' => (float) $record->base_salary > 0 ? (float) $record->base_salary : null, 'from_month' => now()->format('Y-m')])
                    ->form(self::okladFields())
                    ->action(function (array $data, $record) {
                        \App\Services\EmployeePayableService::setOklad($record, (float) $data['amount'], $data['from_month']);
                        \Filament\Notifications\Notification::make()->title('Oklad saqlandi')->body("{$data['from_month']} oydan boshlab")->success()->send();
                    }),
                Tables\Actions\Action::make('okladDelete')
                    ->label('')
                    ->tooltip("Okladni o'chirish")
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => auth()->user()?->isAdmin() && (float) $record->base_salary > 0)
                    ->modalHeading(fn ($record) => "{$record->name} — okladni o'chirish")
                    ->modalDescription(fn ($record) => number_format((float) $record->base_salary, 0, '.', ' ') . " so'm oklad tanlangan oydan boshlab olib tashlanadi. Oldingi oylar va berilgan to'lovlar o'zgarmaydi.")
                    ->modalSubmitActionLabel("O'chirish")
                    ->form(self::okladFields(false))
                    ->action(function (array $data, $record) {
                        \App\Services\EmployeePayableService::setOklad($record, 0, $data['from_month']);
                        \Filament\Notifications\Notification::make()->title("Oklad o'chirildi")->body("{$data['from_month']} oydan boshlab")->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
