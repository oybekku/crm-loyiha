<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\EmployeeInventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InventoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';
    protected static ?string $title = 'Inventarizatsiya';
    protected static ?string $modelLabel = 'Inventar';
    protected static ?string $pluralModelLabel = 'Inventarizatsiya';
    protected static ?string $icon = 'heroicon-o-wrench-screwdriver';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Jihoz / asbob nomi')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('image')
                ->label('Rasmlar')
                ->image()
                ->multiple()
                ->reorderable()
                ->appendFiles()
                ->imageEditor()
                ->directory('inventory')
                ->maxSize(5120)
                ->panelLayout('grid')
                ->columnSpanFull(),

            Forms\Components\TextInput::make('quantity')
                ->label('Miqdori')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required(),

            Forms\Components\TextInput::make('price')
                ->label('Narxi (1 dona, so\'m)')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->required(),

            Forms\Components\Select::make('status')
                ->label('Holati')
                ->options(EmployeeInventory::STATUSES)
                ->default('berilgan')
                ->required(),

            Forms\Components\DatePicker::make('given_at')
                ->label('Berilgan sana')
                ->default(now()),

            Forms\Components\DatePicker::make('returned_at')
                ->label('Qaytarilgan sana'),

            Forms\Components\Textarea::make('note')
                ->label('Izoh')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Rasm')
                    ->square()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Jihoz')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Soni')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Narxi')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . ' so\'m'),

                Tables\Columns\TextColumn::make('total')
                    ->label('Jami')
                    ->state(fn (EmployeeInventory $r) => number_format($r->total, 0, '.', ' ') . ' so\'m'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Holati')
                    ->badge()
                    ->formatStateUsing(fn ($state) => EmployeeInventory::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'berilgan'    => 'success',
                        'qaytarilgan' => 'info',
                        'yaroqsiz'    => 'warning',
                        'yoqolgan'    => 'danger',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('given_at')
                    ->label('Berilgan')
                    ->date('d.m.Y'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Holati')
                    ->options(EmployeeInventory::STATUSES),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Inventar qo\'shish')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['given_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Tahrirlash'),
                Tables\Actions\DeleteAction::make()->label("O'chirish"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label("O'chirish"),
                ]),
            ]);
    }
}
