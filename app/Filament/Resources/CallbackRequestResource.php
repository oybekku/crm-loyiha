<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallbackRequestResource\Pages;
use App\Models\ContactRequest;
use App\Traits\HasMenuPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CallbackRequestResource extends Resource
{
    use HasMenuPermission;

    protected static ?string $model = ContactRequest::class;

    protected static ?string $navigationIcon  = 'heroicon-o-phone-arrow-down-left';
    protected static ?string $navigationLabel = "Qayta qo'ng'iroq";
    protected static ?string $navigationGroup = 'Buyurtmalar';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel      = "Qo'ng'iroq so'rovi";
    protected static ?string $pluralModelLabel = "Qayta qo'ng'iroq";

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('type', ContactRequest::TYPE_QONGIROQ);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('is_handled', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('type')->default(ContactRequest::TYPE_QONGIROQ),

            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('full_name')
                    ->label('F.I.Sh')
                    ->required()
                    ->maxLength(150),

                Forms\Components\TextInput::make('phone')
                    ->label('Telefon')
                    ->required()
                    ->maxLength(30),

                Forms\Components\Toggle::make('is_handled')
                    ->label('Bog\'lanildi')
                    ->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('F.I.Sh')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_handled')
                    ->label('Bog\'lanildi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Qachon')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_handled')
                    ->label('Bog\'lanildi'),
            ])
            ->actions([
                Tables\Actions\Action::make('handled')
                    ->label('Bog\'lanildi deb belgilash')
                    ->icon('heroicon-o-check')
                    ->visible(fn (ContactRequest $record) => ! $record->is_handled)
                    ->action(fn (ContactRequest $record) => $record->update(['is_handled' => true])),

                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCallbackRequests::route('/'),
        ];
    }
}
