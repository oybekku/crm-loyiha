<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = "To'lovlar";

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')
                ->label("Summa (so'm)")
                ->numeric()
                ->required()
                ->minValue(1),

            Forms\Components\DatePicker::make('payment_date')
                ->label("To'lov sanasi")
                ->required()
                ->default(today()),

            Forms\Components\Select::make('method')
                ->label("To'lov usuli")
                ->options(Payment::methodOptions())
                ->required()
                ->default('naqd'),

            Forms\Components\Textarea::make('note')
                ->label('Izoh')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Sana')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label("Summa")
                    ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . " so'm")
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('method')
                    ->label("Usul")
                    ->formatStateUsing(fn($state) => Payment::methodOptions()[$state] ?? $state)
                    ->colors([
                        'success' => 'naqd',
                        'info'    => 'bank',
                        'warning' => 'karta',
                    ]),

                Tables\Columns\TextColumn::make('note')
                    ->label('Izoh')
                    ->limit(40)
                    ->default('—'),
            ])
            ->defaultSort('payment_date', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label("To'lov qo'shish")
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
