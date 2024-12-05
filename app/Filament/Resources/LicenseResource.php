<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseResource\Pages;
use App\Filament\Resources\LicenseResource\RelationManagers;
use App\Models\License;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

class LicenseResource extends Resource
{
    protected static ?string $model = License::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function getModelLabel(): string
    {
        return __('license');
    }

    public static function getPluralModelLabel(): string
    {
        return __('licenses');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('domain')
                    ->label('Domein')
                    ->required(),
                TextInput::make('key')
                    ->label('Sleutel')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->hint(function ($component) {
                        $hint = '
                        <span wire:click="$set(\''.$component->getStatePath().'\', \'' . str()->random(20) . '\')" class="text-xs cursor-pointer">
                            ' . ucfirst(__('generate')) . '
                        </span>
                        ';

                        return new HtmlString($hint);
                    }),
                DateTimePicker::make('expires_at')
                    ->label('Vervalt op'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('domain')
                    ->label('Domein')
                    ->sortable(),
                TextColumn::make('key')
                    ->label('Sleutel')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Actief')
                    ->getStateUsing(fn ($record) => $record->active())
                    ->icon(fn (bool $state): string => match ($state) {
                        true => 'heroicon-o-check-circle',
                        false => 'heroicon-o-x-circle',
                    })
                    ->color(fn (bool $state): string => match ($state) {
                        true => 'success',
                        false => 'danger',
                    })
                    ->tooltip(fn ($record): string => $record->verified_at)
                    ->alignment(Alignment::Center),
                TextColumn::make('expires_at')
                    ->label('Vervalt op')
                    ->sortable(),
            ])
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
            ]);
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
}
