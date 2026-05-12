<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Setting;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label(__('Save changes'))
                ->action('save'),

            Actions\Action::make('preview')
                ->label(__('Preview'))
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn (Invoice $record) => route('invoices.preview', $record))
                ->openUrlInNewTab(),

            Actions\Action::make('send')
                ->label(__('Send invoice'))
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'sent']))
                ->action(fn (Invoice $record) => $this->sendInvoice($record)),

            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->recalculateTotals();
        $this->record->save();
    }

    private function sendInvoice(Invoice $record): void
    {
        $recipient = $record->client->email;

        if (! $recipient) {
            Notification::make()
                ->title(__('Client has no email address'))
                ->danger()
                ->send();

            return;
        }

        $setting = Setting::singleton();
        $settings = $setting->get('invoice', config('invoice'));
        $mail = Mail::to($recipient);

        if ($settings['bcc_email'] ?? null) {
            $mail->bcc($settings['bcc_email']);
        }

        $mail->send(new InvoiceMail($record));

        $record->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Notification::make()
            ->title(__('Invoice sent to :email', ['email' => $recipient]))
            ->success()
            ->send();
    }
}
