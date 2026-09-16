<?php

namespace App\Filament\Resources\Docs\Pages;

use App\Filament\Resources\Docs\DocumentationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDocumentation extends CreateRecord
{
    protected static string $resource = DocumentationResource::class;

    public function getTitle(): string
    {
        return 'Tambah Panduan';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
