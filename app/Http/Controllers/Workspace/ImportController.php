<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Imports\PartnersImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('workspace/import', [
            'result' => session('import_result'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:xlsx,xls,csv,txt'],
        ]);

        try {
            $import = new PartnersImport;
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to import partners: '.$e->getMessage());
        }

        return back()
            ->with('success', "Imported {$import->created} partner(s). Invitations are queued.")
            ->with('import_result', [
                'file' => $request->file('file')->getClientOriginalName(),
                'created' => $import->created,
                'skipped' => $import->skipped,
            ]);
    }
}
