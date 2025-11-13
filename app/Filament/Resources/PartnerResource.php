<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use App\Jobs\SendPartnerEmail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Actions;
use App\Filament\Actions\SendEmailToTiersAction;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Partners';
    protected static ?string $navigationGroup = 'Registration';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Partner Details')
                    ->description('Basic information about the partner')
                    ->schema([
                        Select::make('title')
                        ->options([
                           'Brother' => 'Brother',
                            'Sister' => 'Sister',
                            'Deacon' => 'Deacon',
                            'Deaconess' => 'Deaconess',
                            'Pastor' => 'Pastor',
                            'Reverend' => 'Reverend',
                        ])
                        ->label('Title')
                        ->required(),
                        Select::make('designation')
                            ->options([
                                'Non-Pastoring' => 'Non-Pastoring',
                                'BLW Group Secretary' => 'BLW Group Secretary',
                                'BLW Zonal Secretary' => 'BLW Zonal Secretary',
                                'BLW Regional Secretary' => 'BLW Regional Secretary',
                                'Church Pastor' => 'Church Pastor',
                                'Sub-Group Pastor' => 'Sub-Group Pastor',
                                'Group Pastor' => 'Group Pastor',
                                'Asst. Zonal Pastor' => 'Asst. Zonal Pastor',
                                'Zonal Pastor' => 'Zonal Pastor',
                                'Zonal Director' => 'Zonal Director',
                                'Regional Pastor' => 'Regional Pastor',
                            ])
                            ->label('Designation')
                            ->required(),

                        TextInput::make('full_name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->label('Email')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Section::make('Location Information')
                    ->description('Partner\'s church location details')
                    ->schema([
                        TextInput::make('region')
                            ->label('Region')
                            ->maxLength(255),

                        TextInput::make('zone')
                            ->label('Zone')
                            ->maxLength(255),

                        TextInput::make('group')
                            ->label('Group')
                            ->maxLength(255),

                        TextInput::make('church')
                            ->label('Church')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Partnership Information')
                    ->description('Partnership tier and ROR sponsorship details')
                    ->schema([
                        Select::make('tier')
                            ->options([
                                'ruby' => '💎 Ruby',
                                'silver' => '🥈 Silver',
                                'gold' => '🥇 Gold',
                                'diamond' => '💠 Diamond',
                                'as_one_man' => '💞 As One Man',
                                'top_individual' => '⭐ Top Individual Partner',
                            ])
                            ->label('Partnership Tier')
                            ->required()
                            ->default('ruby')
                            ->helperText('QR code color will be automatically generated based on tier'),

                        TextInput::make('ror_copies_sponsored')
                            ->numeric()
                            ->label('ROR Copies Sponsored')
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('IPPC 2025 Attendance')
                    ->description('Information about IPPC attendance and delivery preferences')
                    ->schema([
Toggle::make('will_attend_ippc')
    ->label('Will you attend IPPC 2025?')
    ->default(false)
    ->live()
    ->afterStateUpdated(function ($state, callable $set, callable $get) {
        if (!$state) {
            // Not attending IPPC = can't attend exhibition
            $set('will_be_at_exhibition', false);
        }
        
        // Clear delivery method if attending both IPPC and exhibition
        if ($state && $get('will_be_at_exhibition')) {
            $set('delivery_method', null);
        }
    }),

Toggle::make('will_be_at_exhibition')
    ->label('Will you be our honoured Guest at the Angel Lounge?')
    ->visible(fn ($get) => $get('will_attend_ippc') === true)
    ->default(false)
    ->live()
    ->afterStateUpdated(function ($state, callable $set, callable $get) {
        // If attending both IPPC and exhibition, clear delivery method
        if ($state && $get('will_attend_ippc')) {
            $set('delivery_method', null);
        }
    }),

    Textarea::make('delivery_method')
        ->label('How should we deliver your ROR gifts?')
        ->placeholder('Please provide the name and contact information for the liaison person.')
        ->visible(function ($get) {
            $attendingIppc = $get('will_attend_ippc');
            $attendingExhibition = $get('will_be_at_exhibition');
            
            // Show delivery method if:
            // 1. Not attending IPPC at all, OR
            // 2. Attending IPPC but NOT attending exhibition
            return !$attendingIppc || ($attendingIppc && !$attendingExhibition);
        })
        ->required(function ($get) {
            $attendingIppc = $get('will_attend_ippc');
            $attendingExhibition = $get('will_be_at_exhibition');
            
            // Required if not attending both events
            return !$attendingIppc || ($attendingIppc && !$attendingExhibition);
        })
        ->rows(3)
        ->maxLength(1000),
                    ])
                    ->columns(1),

                Section::make('Spouse Information')
                    ->description('Add spouse details if coming together')
                    ->schema([
                        Toggle::make('coming_with_spouse')
                            ->label('Coming with spouse?')
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) {
                                    $set('spouse_title', null);
                                    $set('spouse_name', null);
                                    $set('spouse_kc_handle', null);
                                }
                            }),

                        Select::make('spouse_title')
                            ->options([
                                'Brother' => 'Brother',
                                'Sister' => 'Sister',
                                'Deacon' => 'Deacon',
                                'Deaconess' => 'Deaconess',
                                'Pastor' => 'Pastor',
                                'Reverend' => 'Reverend',
                            ])
                            ->label('Spouse Title')
                            ->visible(fn ($get) => $get('coming_with_spouse') === true)
                            ->required(fn ($get) => $get('coming_with_spouse') === true),

                        TextInput::make('spouse_name')
                            ->label('Spouse Name')
                            ->visible(fn ($get) => $get('coming_with_spouse') === true)
                            ->required(fn ($get) => $get('coming_with_spouse') === true)
                            ->maxLength(255),

                        TextInput::make('spouse_kc_handle')
                            ->label('Spouse KC Handle')
                            ->visible(fn ($get) => $get('coming_with_spouse') === true)
                            ->maxLength(255)
                    ])
                    ->columns(2),

                Section::make('Registration & Verification')
                    ->description('Token and registration status information')
                    ->schema([
                        Toggle::make('is_registered')
                            ->label('Is Registered?')
                            ->disabled()
                            ->helperText('Automatically set when token is used'),

                        Forms\Components\Placeholder::make('verification_token')
                            ->label('Verification Token')
                            ->content(fn ($record) => $record?->verification_token ?? 'Not generated yet'),

                        Forms\Components\Placeholder::make('registration_token')
                            ->label('Registration Token')
                            ->content(fn ($record) => $record?->registration_token ?? 'Not generated yet'),

                        Forms\Components\Placeholder::make('token_used_at')
                            ->label('Token Used At')
                            ->content(fn ($record) => $record?->token_used_at 
                                ? $record->token_used_at->format('M d, Y H:i:s') 
                                : 'Not used yet'),
                    ])
                    ->visible(fn ($record) => $record !== null)
                    ->columns(2),

                Section::make('Email Information')
                    ->description('Email sending status and response details')
                    ->schema([
                        Forms\Components\Placeholder::make('email_status_info')
                            ->label('Email Status')
                            ->content(function ($record) {
                                if (!$record) return 'N/A';
                                
                                if ($record->email_sent) {
                                    return '✅ Email sent successfully';
                                } elseif ($record->email_failed) {
                                    return '❌ Email failed to send';
                                } elseif ($record->email_pending) {
                                    return '⏳ Email is pending';
                                } else {
                                    return '❓ No email sent yet';
                                }
                            }),

                        Forms\Components\Placeholder::make('email_response')
                            ->label('Email Response/Error')
                            ->content(fn ($record) => $record?->email_response ?? 'N/A')
                            ->visible(fn ($record) => filled($record?->email_response)),
                    ])
                    ->visible(fn ($record) => $record !== null)
                    ->columns(1),

                Section::make('QR Code')
                    ->description('Tier-specific colored QR code for partner verification')
                    ->schema([
                        ViewField::make('qr_code_path')
                            ->view('filament.components.qr-code')
                            ->label('QR Code')
                            ->visible(fn ($record) => filled($record?->qr_code_path)),
                    ])
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->coming_with_spouse 
                        ? "👥 With: {$record->spouse_title} {$record->spouse_name}" 
                        : null),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('region')
                    ->label('Region')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('zone')
                    ->label('Zone')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('church')
                    ->label('Church')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\BadgeColumn::make('tier')
                    ->label('Tier')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'ruby' => '💎 Ruby',
                        'silver' => '🥈 Silver',
                        'gold' => '🥇 Gold',
                        'diamond' => '💠 Diamond',
                        'as_one_man' => '🎵 As One Man',
                        'top_individual' => '⭐ Top Individual Partner',
                        default => 'No Tier',
                    })
                    ->colors([
                        'danger' => 'ruby',
                        'secondary' => 'silver',
                        'warning' => 'gold',
                        'info' => 'diamond',
                        'success' => 'as_one_man',
                        'primary' => 'top_individual',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('ror_copies_sponsored')
                    ->label('ROR Copies')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\IconColumn::make('will_attend_ippc')
                    ->label('IPPC?')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('will_be_at_exhibition')
                    ->label('Exhibition?')
                    ->boolean()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\IconColumn::make('coming_with_spouse')
                    ->label('W/ Spouse?')
                    ->boolean()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\IconColumn::make('is_registered')
                    ->label('Registered?')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('email_status')
                    ->label('Email Status')
                    ->getStateUsing(function ($record) {
                        if ($record->email_failed) {
                            return 'Failed';
                        } elseif ($record->email_sent) {
                            return 'Sent';
                        } elseif ($record->email_pending) {
                            return 'Pending';
                        } else {
                            return 'Not Sent';
                        }
                    })
                    ->colors([
                        'success' => 'Sent',
                        'warning' => 'Pending',
                        'danger' => 'Failed',
                        'gray' => 'Not Sent',
                    ])
                    ->icon(fn ($state) => match ($state) {
                        'Sent' => 'heroicon-o-check-circle',
                        'Pending' => 'heroicon-o-clock',
                        'Failed' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->tooltip(fn ($record) => $record->email_response ?? 'No additional information')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Registered On')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('token_used_at')
                    ->dateTime()
                    ->label('Token Used')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tier')
                    ->label('Partnership Tier')
                    ->options([
                        'ruby' => '💎 Ruby',
                        'silver' => '🥈 Silver',
                        'gold' => '🥇 Gold',
                        'diamond' => '💠 Diamond', 
                        'as_one_man' => '🤝 As One Man',
                        'top_individual' => '⭐ Top Individual Partner',
                    ]),

                Tables\Filters\SelectFilter::make('email_status')
                    ->label('Email Status')
                    ->options([
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                        'not_sent' => 'Not Sent',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'sent' => $query->where('email_sent', true),
                            'failed' => $query->where('email_failed', true),
                            'pending' => $query->where('email_pending', true),
                            'not_sent' => $query->where('email_sent', false)
                                               ->where('email_failed', false)
                                               ->where('email_pending', false),
                            default => $query,
                        };
                    }),

                Tables\Filters\Filter::make('will_attend_ippc')
                    ->label('Will Attend IPPC')
                    ->query(fn (Builder $query) => $query->where('will_attend_ippc', true)),

                Tables\Filters\Filter::make('will_be_at_exhibition')
                    ->label('Will Be at Exhibition')
                    ->query(fn (Builder $query) => $query->where('will_be_at_exhibition', true)),

                Tables\Filters\Filter::make('coming_with_spouse')
                    ->label('Coming with Spouse')
                    ->query(fn (Builder $query) => $query->where('coming_with_spouse', true)),

                Tables\Filters\Filter::make('is_registered')
                    ->label('Is Registered')
                    ->query(fn (Builder $query) => $query->where('is_registered', true)),
            ])
            ->actions([
                // Regenerate Token Action (for individual records)
                Tables\Actions\Action::make('regenerate_token')
                    ->label('Regenerate Token')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => !$record->is_registered)
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate Registration Token')
                    ->modalDescription(fn ($record) => "This will generate a new registration link for {$record->full_name}. The old link will no longer work.")
                    ->modalSubmitActionLabel('Regenerate & Send Email')
                    ->action(function (Partner $record) {
                        // Regenerate token
                        $record->regenerateRegistrationToken();

                        // Reset email status
                        $record->update([
                            'email_pending' => true,
                            'email_sent' => false,
                            'email_failed' => false,
                            'email_response' => null,
                        ]);

                        // Send new email with new token
                        SendPartnerEmail::dispatch($record);

                        Notification::make()
                            ->title('Token Regenerated')
                            ->body("New registration link has been generated and sent to {$record->email}")
                            ->success()
                            ->send();
                    }),

                // Copy Registration Link Action
                Tables\Actions\Action::make('copy_registration_link')
                    ->label('Copy Link')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('info')
                    ->visible(fn ($record) => !$record->is_registered && $record->registration_token)
                    ->modalHeading('Registration Link')
                    ->modalDescription(fn ($record) => "Copy this link to share with {$record->full_name}:")
                    ->modalContent(fn ($record) => view('filament.modals.copy-link', [
                        'url' => $record->registration_url
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\Action::make('resend_email')
                    ->label('Resend Email')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->visible(fn ($record) => $record->email_failed || !$record->email_sent)
                    ->requiresConfirmation()
                    ->modalHeading('Resend Email')
                    ->modalDescription(fn ($record) => "Are you sure you want to resend the email to {$record->full_name} ({$record->email})?")
                    ->action(function (Partner $record) {
                        $record->update([
                            'email_pending' => true,
                            'email_sent' => false,
                            'email_failed' => false,
                            'email_response' => null,
                        ]);

                        SendPartnerEmail::dispatch($record);

                        Notification::make()
                            ->title('Email Queued')
                            ->body("Email to {$record->full_name} has been queued for sending.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('view_email_details')
                    ->label('Email Details')
                    ->icon('heroicon-o-information-circle')
                    ->color('info')
                    ->visible(fn ($record) => filled($record->email_response))
                    ->modalContent(fn ($record) => view('filament.modals.email-details', [
                        'partner' => $record
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\Action::make('view_spouse_details')
                    ->label('Spouse Info')
                    ->icon('heroicon-o-user-group')
                    ->color('info')
                    ->visible(fn ($record) => $record->coming_with_spouse)
                    ->modalContent(fn ($record) => view('filament.modals.spouse-details', [
                        'partner' => $record
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // ADD QR CODE BULK ACTION HERE
                    Tables\Actions\BulkAction::make('generate_qr_codes')
                        ->label('Generate QR Codes')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Generate QR Codes')
                        ->modalDescription('Generate QR codes for all selected partners? This will create tier-specific colored QR codes for verification.')
                        ->action(function (Collection $records) {
                            $successCount = 0;
                            $failCount = 0;
                            
                            foreach ($records as $record) {
                                try {
                                    $result = $record->generateQrCode();
                                    if ($result) {
                                        $successCount++;
                                    } else {
                                        $failCount++;
                                        \Log::error("Failed to generate QR code for partner {$record->id}");
                                    }
                                } catch (\Exception $e) {
                                    $failCount++;
                                    \Log::error("Exception generating QR code for partner {$record->id}: " . $e->getMessage());
                                }
                            }

                            if ($successCount > 0) {
                                Notification::make()
                                    ->title('QR Codes Generated')
                                    ->body("Successfully generated {$successCount} QR code(s). Failed: {$failCount}")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('QR Generation Failed')
                                    ->body("Failed to generate QR codes for all {$failCount} selected partners. Check logs for details.")
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\BulkAction::make('resend_emails')
                        ->label('Resend Emails')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Resend Emails')
                        ->modalDescription('Are you sure you want to resend emails to all selected partners?')
                        ->action(function (Collection $records) {
                            $count = 0;
                            
                            foreach ($records as $record) {
                                $record->update([
                                    'email_pending' => true,
                                    'email_sent' => false,
                                    'email_failed' => false,
                                    'email_response' => null,
                                ]);

                                SendPartnerEmail::dispatch($record);
                                $count++;
                            }

                            Notification::make()
                                ->title('Emails Queued')
                                ->body("{$count} email(s) have been queued for sending.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('email_statistics')
                    ->label('Email Stats')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalContent(function () {
                        $stats = [
                            'total' => Partner::count(),
                            'sent' => Partner::where('email_sent', true)->count(),
                            'failed' => Partner::where('email_failed', true)->count(),
                            'pending' => Partner::where('email_pending', true)->count(),
                            'not_sent' => Partner::where('email_sent', false)
                                                ->where('email_failed', false)
                                                ->where('email_pending', false)
                                                ->count(),
                            'with_spouse' => Partner::where('coming_with_spouse', true)->count(),
                            'registered' => Partner::where('is_registered', true)->count(),
                            'attending_ippc' => Partner::where('will_attend_ippc', true)->count(),
                            'tiers' => [
                                'ruby' => Partner::where('tier', 'ruby')->count(),
                                'silver' => Partner::where('tier', 'silver')->count(),
                                'gold' => Partner::where('tier', 'gold')->count(),
                                'diamond' => Partner::where('tier', 'diamond')->count(),
                                'as_one_man' => Partner::where('tier', 'as_one_man')->count(),
                                'top_individual' => Partner::where('tier', 'top_individual')->count(),
                            ],
                        ];

                        return view('filament.modals.email-statistics', ['stats' => $stats]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                    Tables\Actions\Action::make('email_gold_diamond')
    ->label('📧 Email Gold & Diamond')
    ->icon('heroicon-o-envelope')
    ->color('warning')
    ->form([
        Select::make('tier')
            ->label('Select Tier')
            ->options([
                'gold' => '🥇 Gold',
                'diamond' => '💠 Diamond',
                'silver' => '🥈 Silver',
            ])
            ->multiple()
            ->default(['silver', 'gold', 'diamond'])
            ->required(),

        TextInput::make('subject')
            ->label('Subject')
            ->required()
            ->default('Dinner at Angel Lounges - IPPC 2025'),

        Textarea::make('message')
            ->label('Message')
            ->required()
            ->rows(10)
            ->default('Dear Esteemed Partners - The Best in the World!

Congratulations on a most impactful IPPC Thursday evening session with our dear Man of God, Pastor Chris Oyakhilome Dsc Dsc DD. What a glorious time of fellowship and impartation!

We are delighted to inform you that dinner will be served immediately after the evening session at our lounges at Angel Court.

Thank you
Angel Lounges Team'),
    ])
    ->action(function (array $data) {
        $partners = Partner::whereIn('tier', $data['tier'])->get();

        if ($partners->isEmpty()) {
            Notification::make()
                ->title('No Partners Found')
                ->body('No partners found for selected tiers.')
                ->danger()
                ->send();
            return;
        }

        foreach ($partners as $partner) {
            SendPartnerEmail::dispatch($partner, $data['subject'], $data['message']);
        }

        Notification::make()
            ->title('Success!')
            ->body("Emails queued for {$partners->count()} partners")
            ->success()
            ->send();
    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getHeaderActions(): array
    {
         return [
            SendEmailToTiersAction::make(),
            // ... other actions
        ];
        
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
            'batch-upload' => Pages\BatchUploadPartners::route('/batch-upload'),
        ];
    }
}