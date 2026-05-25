<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Project;
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

            Forms\Components\Textarea::make('note')
                ->label('Izoh')
                ->rows(2)
                ->columnSpanFull(),
        ]);
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Xizmat qo\'shish'),
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
