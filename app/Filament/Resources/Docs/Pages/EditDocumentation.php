<?php

namespace App\Filament\Resources\Docs\Pages;

use App\Filament\Resources\Docs\DocumentationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditDocumentation extends EditRecord
{
    protected static string $resource = DocumentationResource::class;

    public function getTitle(): string
    {
        return 'Ubah Panduan';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Hapus'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
