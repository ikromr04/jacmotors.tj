<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Dealer;
use App\Models\Document;
use Illuminate\Contracts\View\View;

class DocumentController extends Controller
{
    public function service(): View
    {
        return view('service', [
            'documents' => $this->documents(DocumentType::Regulation),
            'dealers' => Dealer::query()->active()->where('has_service', true)->get(),
        ]);
    }

    public function manuals(): View
    {
        return view('manuals', [
            'documents' => $this->documents(DocumentType::Manual),
        ]);
    }

    private function documents(DocumentType $type)
    {
        return Document::query()->where('type', $type)->with('car')->orderBy('sort')->get();
    }
}
