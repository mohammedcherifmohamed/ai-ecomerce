<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type']);
        $documents = $this->documentService->paginate($filters, 15);

        return view('admin.documents.index', compact('documents'));
    }

    public function store(StoreDocumentRequest $request)
    {
        $this->documentService->create($request->validated(), $request->file('file'), $request->user()->id);

        return redirect()->route('admin.documents.index')->with('success', 'Document uploaded successfully.');
    }

    public function download(int $id)
    {
        return $this->documentService->download($id);
    }

    public function destroy(int $id)
    {
        $this->documentService->delete($id);

        return redirect()->route('admin.documents.index')->with('success', 'Document deleted.');
    }
}
