<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';

    public static function getModelLabel(): string
    {
        return __('Expense');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Expenses');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
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
            ->emptyStateHeading(__('No expenses'));
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label(__('Name'))
                ->required(),
            Forms\Components\TextInput::make('amount')
                ->label(__('Amount'))
                ->required()
                ->prefixIcon('heroicon-o-currency-euro')
                ->numeric()
                ->minValue(0),
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
            Tables\Columns\TextColumn::make('name')
                ->label(__('Name'))
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('amount')
                ->label(__('Amount'))
                ->sortable()
                ->money('EUR')
                ->summarize(
                    Tables\Columns\Summarizers\Summarizer::make()
                        ->label(__('Yearly'))
                        ->money('EUR')
                        ->using(function (Builder $query): float {
                            return $query->get()->sum(
                                fn ($row) => match ($row->frequency) {
                                    'daily' => $row->amount * 365,
                                    'monthly' => $row->amount * 12,
                                    'yearly' => $row->amount,
                                    default => $row->amount * 0,
                                }
                            );
                        })
                ),
            Tables\Columns\TextColumn::make('frequency')
                ->label(__('Frequency'))
                ->searchable()
                ->formatStateUsing(fn (string $state): string => __(ucfirst($state))),
            Tables\Columns\TextColumn::make('start')
                ->label(__('Start date'))
                ->sortable()
                ->date(),
            Tables\Columns\TextColumn::make('end')
                ->label(__('End date'))
                ->sortable()
                ->date(),
        ];
    }
}
