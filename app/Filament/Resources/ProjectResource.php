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
use App\Traits\HasMenuPermission;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    use HasMenuPermission;

    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Loyihalar ro\'yxati';
    protected static ?string $modelLabel = 'Loyiha';
    protected static ?string $pluralModelLabel = 'Loyihalar';
    protected static ?string $navigationGroup = 'Loyihalar';
    protected static ?int $navigationSort = 2;

    // Loyihalar ro'yxati menyu — faqat resource_project ruxsati bo'lganda ko'rinadi
    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        // Loyiha tahrirlash yoki ro'yxat ruxsati bo'lsa — resource sahifalariga kirish mumkin
        return $user->hasPermission(static::menuPermissionKey())
            || $user->hasPermission('loyiha_tahrirlash');
    }

    // Menyu da "Loyihalar ro'yxati" ko'rinishi faqat resource_project ruxsati bo'lganda
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        return $user->hasPermission(static::menuPermissionKey());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();
        if ($user && !$user->canSeeAllProjects()) {
            if ($user->isHisobchi()) {
                $query->where('status', '!=', 'yangi');
            } elseif (!$user->hasPermission('barcha_loyihalar')) {
                $query->whereHas('assignedUsers', fn($q) => $q->where('users.id', $user->id));
            }
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
                        ->extraInputAttributes(['data-map-address' => '1'])
                        ->hint("Quyidagi xaritadan bosib manzilni tanlang"),

                    Forms\Components\View::make('filament.forms.components.map-picker')
                        ->columnSpanFull(),

                    Forms\Components\View::make('filament.forms.components.coord-picker')
                        ->columnSpanFull(),

                    // Yashirin fieldlar — DB ga saqlanadi, coord-picker boshqaradi
                    Forms\Components\TextInput::make('latitude')
                        ->hiddenLabel()->extraInputAttributes(['data-map-address' => '0', 'id' => 'fp-lat-input'])
                        ->numeric()->step(0.000001)->columnSpanFull()
                        ->extraAttributes(['style' => 'display:none']),

                    Forms\Components\TextInput::make('longitude')
                        ->hiddenLabel()->extraInputAttributes(['id' => 'fp-lng-input'])
                        ->numeric()->step(0.000001)->columnSpanFull()
                        ->extraAttributes(['style' => 'display:none']),

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
                    Forms\Components\Placeholder::make('existing_files')
                        ->label('Yuklangan fayllar')
                        ->content(function ($record) {
                            if (!$record) return 'Hali fayl yuklangan emas';
                            $files = $record->files()->orderByDesc('created_at')->get();
                            if ($files->isEmpty()) return 'Hali fayl yuklangan emas';
                            $html = '<div style="display:flex;flex-direction:column;gap:6px">';
                            foreach ($files as $file) {
                                $url  = asset('storage/' . $file->file_path);
                                $ext  = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                $icon = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? '🖼️'
                                      : ($ext === 'pdf' ? '📄'
                                      : (in_array($ext, ['doc','docx']) ? '📝'
                                      : (in_array($ext, ['xls','xlsx']) ? '📊' : '📎')));
                                $size = $file->file_size ? round($file->file_size / 1024) . ' KB' : '';
                                $target = in_array($ext, ['pdf','jpg','jpeg','png','gif','webp']) ? '_blank' : '_self';
                                $dl = in_array($ext, ['doc','docx','xls','xlsx']) ? 'download' : '';
                                $html .= "<a href=\"{$url}\" target=\"{$target}\" {$dl} style=\"display:flex;align-items:center;gap:8px;padding:7px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;text-decoration:none;color:#374151;font-size:13px\">
                                    <span style=\"font-size:16px\">{$icon}</span>
                                    <span style=\"flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap\">{$file->file_name}</span>
                                    <span style=\"color:#9ca3af;font-size:11px;flex-shrink:0\">{$size}</span>
                                </a>";
                            }
                            $html .= '</div>';
                            return new \Illuminate\Support\HtmlString($html);
                        })
                        ->columnSpanFull()
                        ->hidden(fn ($record) => !$record),
                    Forms\Components\FileUpload::make('uploaded_files')
                        ->label('Yangi fayl yuklash')
                        ->multiple()
                        ->disk('public')
                        ->directory(fn($record) => $record
                            ? 'project-files/' . $record->id
                            : 'project-files/tmp')
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
                        ->default('turar')
                        ->disabled(fn() => !in_array(auth()->user()?->role, ['admin', 'menejer']))
                        ->dehydrated(fn() => in_array(auth()->user()?->role, ['admin', 'menejer'])),

                    Forms\Components\Select::make('status')
                        ->label('Holat')
                        ->options(function (?Project $record) {
                            $all = \App\Models\ProjectStatus::asOptions();
                            $user = auth()->user();
                            if ($user && !in_array($user->role, ['admin', 'menejer'])) {
                                $current = $record?->status ?? 'yangi';
                                $allowed = [];
                                if (isset($all[$current])) $allowed[$current] = $all[$current];
                                if (isset($all['tekshirish'])) $allowed['tekshirish'] = $all['tekshirish'];
                                return $allowed;
                            }
                            return $all;
                        })
                        ->required()
                        ->default('yangi'),

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

                Tables\Columns\TextColumn::make('services_performance')
                    ->label('Ish ko\'rsatkichi')
                    ->html()
                    ->state(function (Project $record): string {
                        $record->loadMissing(['services.assignedUser', 'statusLogs']);
                        $rows = '';
                        foreach ($record->services as $svc) {
                            if (!$svc->assigned_user_id) continue;
                            $name     = $svc->assignedUser?->name ?? '—';
                            $log      = $record->statusLogs->where('status', $svc->service_name)->first();
                            $svcLabel = \App\Models\Project::serviceOptions()[$svc->service_name] ?? $svc->service_name;
                            $given    = $svc->deadline_days;
                            if ($svc->work_started_at && $svc->deadline_days) {
                                if ($log?->left_at) {
                                    $took  = (int) \Carbon\Carbon::parse($svc->work_started_at)->diffInDays($log->left_at);
                                    $diff  = $took - $given;
                                    $badge = $diff <= 0
                                        ? "<span style='background:#dcfce7;color:#16a34a;border-radius:4px;padding:1px 5px;font-size:10px;font-weight:700'>✓ {$took}/{$given} kun</span>"
                                        : "<span style='background:#fee2e2;color:#dc2626;border-radius:4px;padding:1px 5px;font-size:10px;font-weight:700'>+{$diff} kun kechikdi</span>";
                                } else {
                                    $elapsed = (int) \Carbon\Carbon::parse($svc->work_started_at)->diffInDays(now());
                                    $diff    = $elapsed - $given;
                                    $remaining = $given - $elapsed;
                                    if ($diff > 0) {
                                        $badge = "<span style='background:#fee2e2;color:#dc2626;border-radius:4px;padding:1px 5px;font-size:10px;font-weight:700;animation:blink-warn 1s ease-in-out infinite;display:inline-block'>+{$diff} kun!</span>";
                                    } elseif ($remaining <= 3) {
                                        $badge = "<span style='background:#fee2e2;color:#dc2626;border-radius:4px;padding:1px 5px;font-size:10px;font-weight:700;animation:blink-warn 1s ease-in-out infinite;display:inline-block'>{$remaining} kun</span>";
                                    } else {
                                        $badge = "<span style='background:#e0f2fe;color:#0284c7;border-radius:4px;padding:1px 5px;font-size:10px;font-weight:700'>⏳ {$elapsed}/{$given} kun</span>";
                                    }
                                }
                            } else {
                                $badge = "<span style='background:#f3f4f6;color:#9ca3af;border-radius:4px;padding:1px 5px;font-size:10px'>—</span>";
                            }
                            $rows .= "<div style='font-size:11px;margin-bottom:3px'><span style='color:#6b7280'>{$svcLabel}:</span> <span style='font-weight:600'>{$name}</span> {$badge}</div>";
                        }
                        return $rows ?: '<span style="color:#d1d5db;font-size:11px">—</span>';
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

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

                SelectFilter::make('assignedUsers')
                    ->label("Hodim")
                    ->relationship('assignedUsers', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('print_ariza')
                    ->label('Ariza')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (Project $record) => route('print.project.ariza', $record))
                    ->openUrlInNewTab(),
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
