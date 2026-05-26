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
                    ->required(),

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
                    ->required(fn($operation) => $operation === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->placeholder('Yangi parol kiriting'),
            ]),
        ]);
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
                    ->label('Email')
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

                Tables\Columns\TextColumn::make('projects_count')
                    ->label('Loyihalar')
                    ->counts('projects')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
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
