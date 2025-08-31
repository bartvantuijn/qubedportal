<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseResource\Pages;
use App\Models\License;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LicenseResource extends Resource
{
    protected static ?string $model = License::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function getModelLabel(): string
    {
        return __('License');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Licenses');
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->client->name . ' ' . $record->domain;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['client.name', 'domain'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll()
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
            ->emptyStateHeading(__('No licenses'));
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
            'index' => Pages\ListLicenses::route('/'),
            'create' => Pages\CreateLicense::route('/create'),
            'edit' => Pages\EditLicense::route('/{record}/edit'),
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
            Forms\Components\TextInput::make('domain')
                ->label(__('Domain'))
                ->required(),
            Forms\Components\TextInput::make('key')
                ->label(__('Key'))
                ->required()
                ->unique(ignoreRecord: true)
                ->suffixAction(
                    Forms\Components\Actions\Action::make('generate')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function (Set $set) {
                            $set('key', str()->random(20));
                        }),
                ),
            Forms\Components\DateTimePicker::make('expires_at')
                ->label(__('Expires at')),
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
            Tables\Columns\TextColumn::make('domain')
                ->label(__('Domain'))
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('key')
                ->label(__('Key'))
                ->sortable(),
            Tables\Columns\IconColumn::make('active')
                ->label(__('Active'))
                ->getStateUsing(fn (License $license): bool => $license->active())
                ->icon(fn (bool $state): string => match ($state) {
                    true => 'heroicon-o-check-circle',
                    false => 'heroicon-o-x-circle',
                })
                ->color(fn (bool $state): string => match ($state) {
                    true => 'success',
                    false => 'danger',
                })
                ->tooltip(fn (License $license): ?string => $license->verified_at)
                ->alignment(Enums\Alignment::Center),
            Tables\Columns\TextColumn::make('expires_at')
                ->label(__('Expires at'))
                ->sortable(),
            //])->from('md')
        ];
    }
}
