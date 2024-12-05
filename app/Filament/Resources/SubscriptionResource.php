<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\RelationManagers;
use App\Models\Product;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    public static function getModelLabel(): string
    {
        return __('Subscription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Subscriptions');
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->client->name . ' ' . $record->product->name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['client.name', 'product.name', 'description'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
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
            ])
            ->emptyStateHeading(__('No subscriptions'));
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('client_id')
                ->label(__('Client'))
                ->required()
                ->relationship(name: 'client', titleAttribute: 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('product_id')
                ->label(__('Product'))
                ->required()
                ->relationship(name: 'product', titleAttribute: 'name')
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(fn (Set $set, ?int $state) => $set('price', Product::find($state)?->price)),
            Forms\Components\TextInput::make('price')
                ->label(__('Price'))
                ->required()
                ->prefixIcon('heroicon-o-currency-euro')
                ->mask(RawJs::make('$money($input)')),
            Forms\Components\Select::make('frequency')
                ->label(__('Frequency'))
                ->required()
                ->native(false)
                ->options([
                    'daily' => __('Daily'),
                    'monthly' => __('Monthly'),
                    'yearly' => __('Yearly'),
                    'none' => __('None'),
                ]),
            Forms\Components\DatePicker::make('start')
                ->label(__('Start date'))
                ->native(false)
                ->live(onBlur: true)
                ->maxDate(fn (Get $get) => $get('end') ?: null)
                ->closeOnDateSelection(),
            Forms\Components\DatePicker::make('end')
                ->label(__('End date'))
                ->native(false)
                ->live(onBlur: true)
                ->minDate(fn (Get $get) => $get('start') ?: null)
                ->closeOnDateSelection(),
            Forms\Components\RichEditor::make('description')
                ->label(__('Description'))
                ->columnSpan('full'),
        ];
    }

    public static function getTableColumns(): array
    {
        return [
            //Tables\Columns\Layout\Split::make([
                Tables\Columns\TextColumn::make('client.name')
                    ->label(__('Client'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('Product'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->sortable()
                    ->money('EUR')
                    ->summarize(
                        Tables\Columns\Summarizers\Summarizer::make()
                            ->label(__('Yearly'))
                            ->money('EUR')
                            ->using(function (Builder $query): float {
                                return $query->get()->sum(fn ($row) =>
                                    match ($row->frequency) {
                                        'daily' => $row->price * 365,
                                        'monthly' => $row->price * 12,
                                        'yearly' => $row->price,
                                        default => $row->price * 0,
                                    }
                                );
                            })
                    ),
                Tables\Columns\TextColumn::make('frequency')
                    ->label(__('Frequency'))
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => __(ucfirst($state))),
                Tables\Columns\TextColumn::make('start')
                    ->label(__('Start date'))
                    ->sortable()
                    ->date(),
                Tables\Columns\TextColumn::make('end')
                    ->label(__('End date'))
                    ->sortable()
                    ->date(),
            //])->from('md')
        ];
    }
}
