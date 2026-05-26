<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicePriceTierResource\Pages;
use App\Models\ServicePriceTier;
use App\Traits\HasMenuPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServicePriceTierResource extends Resource
{
    use HasMenuPermission;

    protected static ?string $model = ServicePriceTier::class;
    protected static ?string $navigationIcon        = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel       = 'Xizmat narxlari';
    protected static ?string $navigationGroup       = 'Sozlamalar';
    protected static ?string $modelLabel            = 'Narx';
    protected static ?string $pluralModelLabel      = 'Xizmat narxlari';
    protected static ?int    $navigationSort        = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('service_key')
                ->label('Xizmat')
                ->options([
                    'toposyomka'   => 'Toposyomka',
                    'eskiz_loyiha' => 'Eskiz loyiha',
                    'ariza'        => 'Ariza',
                ])
                ->required(),

            Forms\Components\TextInput::make('sub_service')
                ->label('Kichik bo\'lim (key)')
                ->required(),

            Forms\Components\TextInput::make('sub_service_label')
                ->label('Kichik bo\'lim nomi')
                ->required(),

            Forms\Components\TextInput::make('label')
                ->label('Tarif nomi')
                ->required()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('price')
                ->label('Narx (so\'m)')
                ->numeric()
                ->required()
                ->minValue(0),

            Forms\Components\TextInput::make('sort_order')
                ->label('Tartib')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        $serviceLabels = [
            'toposyomka'   => 'Toposyomka',
            'eskiz_loyiha' => 'Eskiz loyiha',
            'ariza'        => 'Ariza',
        ];

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_key')
                    ->label('Xizmat')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $serviceLabels[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'toposyomka'   => 'info',
                        'eskiz_loyiha' => 'success',
                        'ariza'        => 'warning',
                        default        => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('sub_service_label')
                    ->label('Kichik bo\'lim')
                    ->sortable(),

                Tables\Columns\TextColumn::make('label')
                    ->label('Tarif')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Narx')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . ' so\'m')
                    ->sortable(),
            ])
            ->defaultSort('service_key')
            ->filters([
                Tables\Filters\SelectFilter::make('service_key')
                    ->label('Xizmat')
                    ->options($serviceLabels),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Tahrirlash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label("O'chirish"),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServicePriceTiers::route('/'),
            'create' => Pages\CreateServicePriceTier::route('/create'),
            'edit'   => Pages\EditServicePriceTier::route('/{record}/edit'),
        ];
    }
}
