<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Repositories\Interfaces\DocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessDocumentJob;

class DocumentService
{
    public function __construct(
        protected DocumentRepositoryInterface $documentRepository,
    ) {}

    public function getById(int $id)
    {
        return $this->documentRepository->findById($id);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->documentRepository->paginate($filters, $perPage);
    }

    public function create(array $data, $file, int $uploadedBy)
    {
        $path = $file->store('documents', 'public');

        $document =  $this->documentRepository->create([
            'uploaded_by' => $uploadedBy,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? DocumentType::Other,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        ProcessDocumentJob::dispatch($document);
        
        return $document ;

    }

    public function update(int $id, array $data)
    {
        return $this->documentRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $document = $this->documentRepository->findById($id);
        Storage::disk('public')->delete($document->file_path);

        return $this->documentRepository->delete($id);
    }

    public function download(int $id)
    {
        $document = $this->documentRepository->findById($id);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function count(): int
    {
        return $this->documentRepository->count();
    }
}
