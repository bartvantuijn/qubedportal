<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    public Setting $setting;

    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.settings';

    public function getTitle(): string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function mount(): void
    {
        $this->setting = Setting::singleton();

        $this->form->fill([
            'invoice' => $this->setting->get('invoice', config('invoice')),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Invoice'))
                    ->schema([
                        Forms\Components\TextInput::make('invoice.company_name')
                            ->label(__('Company name')),
                        Forms\Components\TextInput::make('invoice.email')
                            ->label(__('Email'))
                            ->email(),
                        Forms\Components\TextInput::make('invoice.bcc_email')
                            ->label(__('BCC email'))
                            ->email(),
                        Forms\Components\TextInput::make('invoice.phone')
                            ->label(__('Phone'))
                            ->tel(),
                        Forms\Components\TextInput::make('invoice.website')
                            ->label(__('Website')),
                        Forms\Components\TextInput::make('invoice.street')
                            ->label(__('Street')),
                        Forms\Components\TextInput::make('invoice.postcode')
                            ->label(__('Postcode')),
                        Forms\Components\TextInput::make('invoice.city')
                            ->label(__('City')),
                        Forms\Components\TextInput::make('invoice.kvk')
                            ->label(__('KVK')),
                        Forms\Components\TextInput::make('invoice.vat_number')
                            ->label(__('VAT number')),
                        Forms\Components\TextInput::make('invoice.iban')
                            ->label(__('IBAN')),
                        Forms\Components\TextInput::make('invoice.bic')
                            ->label(__('BIC')),
                    ])
                    ->columns(2),
            ])
            ->statePath('data')
            ->model($this->setting);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save'))
                ->submit('save')
                ->formId('settings-form'),
        ];
    }

    public function save(): void
    {
        $this->setting = Setting::singleton();
        $state = $this->form->getState();

        $this->setting->set('invoice', $state['invoice'] ?? []);

        Notification::make()
            ->title(__('Settings saved successfully.'))
            ->success()
            ->send();
    }
}
