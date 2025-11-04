<?php

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PartnersImport;

class BatchUploadPartners extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = PartnerResource::class;

    protected static ?string $navigationLabel = 'Batch Upload';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Registration';

    protected static string $view = 'filament.resources.partner-resource.pages.batch-upload-partners';

    // Form data property
    public ?array $data = [];

    // Mount method to initialize the form
    public function mount(): void
    {
        $this->form->fill();
    }

    // Define the form
    public function form(Form $form): Form
{
    return $form
        ->schema([
            FileUpload::make('excel_file')
                ->label('Upload Excel/CSV')
                ->acceptedFileTypes([
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'text/csv',
                ])
                ->disk('local')  // Explicitly set the disk
                ->directory('excel_uploads')
                ->storeFileNamesIn('excel_file_name')  // Store original filename
                ->required()
                ->maxSize(10240), // 10MB max
        ])
        ->statePath('data');
}

    // Submit method
public function submit(): void
{
    try {
        // Validate the form
        $data = $this->form->getState();

        // Get the uploaded file
        $uploadedFile = is_array($data['excel_file']) ? $data['excel_file'][0] : $data['excel_file'];
        
        // Get the real path
        $filePath = Storage::disk('local')->path($uploadedFile);
        
        // Check Livewire temporary path if not found
        if (!file_exists($filePath)) {
            $filePath = storage_path('app/livewire-tmp/' . $uploadedFile);
        }

        if (!file_exists($filePath)) {
            Notification::make()
                ->title('Error!')
                ->body('Uploaded file not found.')
                ->danger()
                ->send();
            return;
        }

        // Import the file
        Excel::import(new PartnersImport, $filePath);

        // Success notification
        Notification::make()
            ->title('Success!')
            ->body('Partners imported and emails queued successfully!')
            ->success()
            ->send();

        // Reset the form
        $this->form->fill();

    } catch (\Exception $e) {
        Notification::make()
            ->title('Error!')
            ->body('Failed to import partners: ' . $e->getMessage())
            ->danger()
            ->send();
    }
}
}