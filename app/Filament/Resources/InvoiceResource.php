<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getModelLabel(): string
    {
        return __('Invoice');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Invoices');
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->invoice_number . ' — ' . $record->client->name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['invoice_number', 'client.name'];
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
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'sent' => __('Sent'),
                        'paid' => __('Paid'),
                        'overdue' => __('Overdue'),
                        'cancelled' => __('Cancelled'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('No invoices'))
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make(__('Invoice details'))
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->label(__('Client'))
                        ->required()
                        ->relationship(name: 'client', titleAttribute: 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('invoice_number')
                        ->label(__('Invoice number'))
                        ->required()
                        ->default(fn () => Invoice::nextInvoiceNumber()),
                    Forms\Components\ToggleButtons::make('status')
                        ->label(__('Status'))
                        ->options([
                            'draft' => __('Draft'),
                            'sent' => __('Sent'),
                            'paid' => __('Paid'),
                            'overdue' => __('Overdue'),
                            'cancelled' => __('Cancelled'),
                        ])
                        ->colors([
                            'draft' => 'gray',
                            'sent' => 'info',
                            'paid' => 'success',
                            'overdue' => 'danger',
                            'cancelled' => 'gray',
                        ])
                        ->default('draft')
                        ->grouped()
                        ->inline()
                        ->required()
                        ->columnSpan('full'),
                    Forms\Components\DatePicker::make('issue_date')
                        ->label(__('Issue date'))
                        ->required()
                        ->native(false)
                        ->default(now())
                        ->closeOnDateSelection(),
                    Forms\Components\DatePicker::make('due_date')
                        ->label(__('Due date'))
                        ->required()
                        ->native(false)
                        ->default(now()->addDays(30))
                        ->closeOnDateSelection(),
                ]),

            Forms\Components\Section::make(__('Invoice items'))
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label(__('Items'))
                        ->relationship('items')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label(__('Product'))
                                ->relationship(name: 'product', titleAttribute: 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                    if (! $state) {
                                        return;
                                    }

                                    $product = Product::find($state);

                                    if (! $product) {
                                        return;
                                    }

                                    $set('title', $product->name);
                                    $set('unit_price', $product->price ?? 0);
                                    self::recalculateItem($get, $set);
                                    self::recalculateTotals($get, $set, '../../');
                                })
                                ->columnSpan('full'),
                            Forms\Components\TextInput::make('title')
                                ->label(__('Title'))
                                ->required()
                                ->columnSpan('full'),
                            Forms\Components\TextInput::make('subtitle')
                                ->label(__('Subtitle'))
                                ->columnSpan('full'),
                            Forms\Components\TextInput::make('unit_price')
                                ->label(__('Unit price'))
                                ->required()
                                ->numeric()
                                ->prefixIcon('heroicon-o-currency-euro')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set): void {
                                    self::recalculateItem($get, $set);
                                    self::recalculateTotals($get, $set, '../../');
                                }),
                            Forms\Components\TextInput::make('quantity')
                                ->label(__('Qty'))
                                ->required()
                                ->integer()
                                ->default(1)
                                ->minValue(1)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set): void {
                                    self::recalculateItem($get, $set);
                                    self::recalculateTotals($get, $set, '../../');
                                }),
                            Forms\Components\TextInput::make('total')
                                ->label(__('Total'))
                                ->numeric()
                                ->prefixIcon('heroicon-o-currency-euro')
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columns(3)
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set))
                        ->addActionLabel(__('Add item'))
                        ->defaultItems(1),
                ]),

            Forms\Components\Section::make(__('Totals & VAT'))
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('tax_rate')
                        ->label(__('VAT rate'))
                        ->required()
                        ->numeric()
                        ->default(21)
                        ->suffix('%')
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set)),
                    Forms\Components\TextInput::make('subtotal')
                        ->label(__('Subtotal'))
                        ->prefixIcon('heroicon-o-currency-euro')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),
                    Forms\Components\TextInput::make('tax_amount')
                        ->label(__('VAT amount'))
                        ->prefixIcon('heroicon-o-currency-euro')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),
                    Forms\Components\TextInput::make('total')
                        ->label(__('Total incl. VAT'))
                        ->prefixIcon('heroicon-o-currency-euro')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),
                ]),

        ];
    }

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('invoice_number')
                ->label(__('Invoice number'))
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('client.name')
                ->label(__('Client'))
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->formatStateUsing(fn (string $state): string => __(ucfirst($state)))
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'sent' => 'info',
                    'paid' => 'success',
                    'overdue' => 'danger',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('total')
                ->label(__('Total'))
                ->money('EUR')
                ->sortable(),
            Tables\Columns\TextColumn::make('due_date')
                ->label(__('Due date'))
                ->sortable()
                ->date(),
            Tables\Columns\TextColumn::make('paid_at')
                ->label(__('Paid at'))
                ->sortable()
                ->dateTime()
                ->placeholder('—'),
        ];
    }

    public static function recalculateItem(Get $get, Set $set): void
    {
        $set('total', round((float) $get('unit_price') * max(1, (int) $get('quantity')), 2));
    }

    public static function recalculateTotals(Get $get, Set $set, string $statePathPrefix = ''): void
    {
        $totals = self::calculateTotals(
            $get($statePathPrefix . 'items') ?? [],
            (float) ($get($statePathPrefix . 'tax_rate') ?? 0),
        );

        $set($statePathPrefix . 'subtotal', $totals['subtotal']);
        $set($statePathPrefix . 'tax_amount', $totals['tax_amount']);
        $set($statePathPrefix . 'total', $totals['total']);
    }

    private static function calculateTotals(array $items, float $taxRate): array
    {
        $subtotal = collect($items)->sum(fn ($item) => round(
            (float) ($item['unit_price'] ?? 0) * max(1, (int) ($item['quantity'] ?? 1)),
            2,
        ));
        $taxAmount = round($subtotal * $taxRate / 100, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => $taxAmount,
            'total' => round($subtotal + $taxAmount, 2),
        ];
    }
}
