<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';
    protected static ?string $title = 'Fayllar';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('file_path')
                ->label('Fayl')
                ->required()
                ->disk('public')
                ->directory('project-files')
                ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                ->maxSize(20480)
                ->columnSpanFull()
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    if ($state) {
                        $set('file_name', is_string($state) ? basename($state) : $state->getClientOriginalName());
                        $set('file_type', is_string($state) ? '' : $state->getMimeType());
                        $set('file_size', is_string($state) ? 0 : $state->getSize());
                    }
                }),

            Forms\Components\TextInput::make('file_name')
                ->label('Fayl nomi')
                ->required(),

            Forms\Components\Select::make('category')
                ->label('Toifa')
                ->options([
                    'hujjat'     => 'Hujjat',
                    'ruxsatnoma' => 'Ruxsatnoma',
                    'chizma'     => 'Chizma',
                    'boshqa'     => 'Boshqa',
                ])
                ->default('hujjat'),

            Forms\Components\Hidden::make('file_type'),
            Forms\Components\Hidden::make('file_size'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                Tables\Columns\TextColumn::make('file_name')
                    ->label('Fayl nomi')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('Toifa')
                    ->formatStateUsing(fn($state) => match($state) {
                        'hujjat'     => 'Hujjat',
                        'ruxsatnoma' => 'Ruxsatnoma',
                        'chizma'     => 'Chizma',
                        default      => 'Boshqa',
                    }),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Hajmi')
                    ->formatStateUsing(fn($state) => $state ? round($state / 1024, 1) . ' KB' : '—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yuklangan')
                    ->date('d.m.Y'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Fayl yuklash')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Ko\'rish')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn($record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('download')
                    ->label('Yuklab olish')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn($record) => response()->download(Storage::path($record->file_path), $record->file_name)),
                Tables\Actions\DeleteAction::make()->label(''),
            ]);
    }
}
