<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Loyihalar ro\'yxati';
    protected static ?string $modelLabel = 'Loyiha';
    protected static ?string $pluralModelLabel = 'Loyihalar';
    protected static ?string $navigationGroup = 'Loyihalar';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();
        if ($user && !$user->canSeeAllProjects()) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_user_id', $user->id);
                if ($user->isHisobchi()) {
                    $q->orWhere('status', 'tolov_jarayonida');
                }
            });
        }
        return $query;
    }

    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'menejer']);
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Asosiy ma'lumotlar")
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('owner_name')
                        ->label('Loyiha egasining ismi')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('title')
                        ->label('Loyiha nomi')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('address')
                        ->label('Manzil')
                        ->required()
                        ->columnSpanFull()
                        ->hint("Quyidagi xaritadan bosib manzilni tanlang"),

                    Forms\Components\View::make('filament.forms.components.map-picker')
                        ->columnSpanFull()
                        ->hidden(fn($operation) => $operation === 'edit'),

                    Forms\Components\Repeater::make('phones')
                        ->label('Telefon raqamlar')
                        ->schema([
                            Forms\Components\TextInput::make('phone')
                                ->label('Raqam')
                                ->tel()
                                ->required()
                                ->placeholder('+998 XX XXX XX XX'),
                        ])
                        ->minItems(1)
                        ->maxItems(5)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label('Qo\'shimcha ma\'lumot')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Hujjatlar')
                ->schema([
                    Forms\Components\FileUpload::make('uploaded_files')
                        ->label('Fayllar yuklash')
                        ->multiple()
                        ->disk('public')
                        ->directory('project-files')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/*',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(20480)
                        ->maxFiles(10)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Loyiha holati')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('category')
                        ->label('Kategoriya')
                        ->options(Project::categoryOptions())
                        ->required()
                        ->default('turar'),

                    Forms\Components\Select::make('status')
                        ->label('Holat')
                        ->options(Project::statusOptions())
                        ->required()
                        ->default('yangi'),

                    Forms\Components\Select::make('assigned_user_id')
                        ->label("Mas'ul xodim")
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $statusColors = [
            'yangi'            => 'info',
            'tolov_jarayonida' => 'warning',
            'tekshirish'       => 'warning',
            'tolangan'         => 'success',
            'tugallangan'      => 'success',
            'taqdim_etilgan'   => 'gray',
            'bekor_qilingan'   => 'danger',
        ];

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Raqam')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('owner_name')
                    ->label('Egasi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Manzil')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('Kategoriya')
                    ->formatStateUsing(fn($state) => Project::categoryOptions()[$state] ?? $state)
                    ->colors(['primary' => fn() => true]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Holat')
                    ->formatStateUsing(fn($state) => Project::statusOptions()[$state] ?? $state)
                    ->colors($statusColors),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label("Mas'ul")
                    ->default('—'),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Umumiy')
                    ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . " so'm")
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label("To'langan")
                    ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . " so'm")
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sana')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Holat')
                    ->options(Project::statusOptions()),

                SelectFilter::make('category')
                    ->label('Kategoriya')
                    ->options(Project::categoryOptions()),

                SelectFilter::make('assigned_user_id')
                    ->label("Mas'ul xodim")
                    ->options(User::pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label(''),
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
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
            RelationManagers\ServicesRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\FilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit'   => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
