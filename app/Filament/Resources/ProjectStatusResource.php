<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectStatusResource\Pages;
use App\Models\ProjectStatus;
use App\Traits\HasMenuPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProjectStatusResource extends Resource
{
    use HasMenuPermission;

    protected static ?string $model = ProjectStatus::class;

    protected static ?string $navigationIcon  = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Loyiha bo\'limlari';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel      = 'Bo\'lim';
    protected static ?string $pluralModelLabel = 'Bo\'limlar';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('label')
                    ->label('Nomi')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set, $record) =>
                        $record === null
                            ? $set('key', Str::snake(transliterator_transliterate('Any-Latin; Latin-ASCII', $state)))
                            : null
                    ),

                Forms\Components\TextInput::make('key')
                    ->label('Kalit (key)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(60)
                    ->helperText("Faqat kichik harf, raqam va _ belgisi. Masalan: yangi_loyiha")
                    ->rules(['regex:/^[a-z0-9_]+$/']),

                Forms\Components\ColorPicker::make('color')
                    ->label('Rangi')
                    ->required()
                    ->default('#6b7280'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Tartib raqami')
                    ->numeric()
                    ->default(10)
                    ->required(),

                Forms\Components\Toggle::make('is_archive')
                    ->label('Arxiv bo\'limi')
                    ->helperText('Yoqilsa, bu bo\'limdagi loyihalar Arxiv sahifasiga o\'tadi')
                    ->default(false),

                Forms\Components\Toggle::make('is_hidden')
                    ->label('Yashirilgan')
                    ->helperText('Yoqilsa, bu bo\'lim Kanban va menyudan butunlay yashiriladi')
                    ->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\ColorColumn::make('color')
                    ->label('Rang'),

                Tables\Columns\TextColumn::make('label')
                    ->label('Nomi')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Kalit')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_archive')
                    ->label('Arxiv')
                    ->boolean()
                    ->trueIcon('heroicon-o-archive-box')
                    ->falseIcon('heroicon-o-view-columns')
                    ->trueColor('warning')
                    ->falseColor('success'),

                Tables\Columns\IconColumn::make('is_hidden')
                    ->label('Yashirin')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('danger')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Qo\'shilgan')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->requiresConfirmation()
                    ->modalHeading('Telegram tasdiqlash kodi')
                    ->modalDescription('Tahrirlash uchun Telegramingizga yuborilgan kodni kiriting')
                    ->mountUsing(function ($record) {
                        \App\Services\TelegramOtpService::sendOtp(
                            auth()->user(), 'status_edit',
                            "Bosqichni tahrirlash: " . ($record->label ?? $record->key)
                        );
                    })
                    ->form(fn (Forms\Form $form) => $form->schema([
                        Forms\Components\TextInput::make('_pin')
                            ->label('Telegram kodi')
                            ->password()
                            ->required()
                            ->placeholder('······'),
                    ]))
                    ->before(function (array $data, $record, Tables\Actions\EditAction $action) {
                        if (!\App\Services\TelegramOtpService::verifyOtp(auth()->user(), $data['_pin'] ?? '', 'status_edit')) {
                            $action->halt();
                            \Filament\Notifications\Notification::make()
                                ->title("Noto'g'ri yoki eskirgan kod")
                                ->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProjectStatuses::route('/'),
            'create' => Pages\CreateProjectStatus::route('/create'),
            'edit'   => Pages\EditProjectStatus::route('/{record}/edit'),
        ];
    }
}
