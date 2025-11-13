<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use App\Models\Partner;
use Illuminate\Support\Facades\Mail;

class SendEmailToTiersAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'send-email-to-tiers')
            ->label('📧 Send Email to Tiers')
            ->icon('heroicon-o-envelope')
            ->color('warning')
            ->form([
                Select::make('tiers')
                    ->label('Select Tiers')
                    ->multiple()
                    ->options([
                        'Gold' => 'Gold',
                        'Diamond' => 'Diamond',
                    ])
                    ->required()
                    ->default(['Gold', 'Diamond']),

                TextInput::make('subject')
                    ->label('Email Subject')
                    ->required()
                    ->placeholder('Enter email subject'),

                Textarea::make('message')
                    ->label('Email Message')
                    ->required()
                    ->rows(8)
                    ->placeholder('Enter your email message'),
            ])
            ->action(function (array $data) {
                // Get partners by selected tiers
                $partners = Partner::whereIn('tier_display', $data['tiers'])
                    ->pluck('email') // Make sure your Partner model has email column
                    ->toArray();

                if (empty($partners)) {
                    session()->flash('error', 'No partners found for selected tiers.');
                    return;
                }

                // Send emails
                foreach ($partners as $email) {
                    Mail::send('emails.tier-notification', [
                        'subject' => $data['subject'],
                        'message' => $data['message'],
                    ], function ($mail) use ($email, $data) {
                        $mail->to($email)
                            ->subject($data['subject']);
                    });
                }

                session()->flash('success', 'Emails sent to ' . count($partners) . ' partners!');
            });
    }
}