<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminLogController extends Controller
{
    private string $logsPath;

    public function __construct()
    {
        $this->logsPath = storage_path('logs');
    }

    public function index(Request $request)
    {
        $files = collect(File::files($this->logsPath))
            ->map(fn ($file) => $file->getFilename())
            ->filter(fn ($name) => str_ends_with($name, '.log'))
            ->sortDesc()
            ->values();

        $selected = basename($request->query('file', $files->first(fn ($f) => str_starts_with($f, 'sprintpay')) ?? $files->first() ?? ''));

        if ($selected && !$files->contains($selected)) {
            $selected = $files->first();
        }

        $content = '';
        $lines = collect();

        if ($selected && File::exists($this->logsPath . '/' . $selected)) {
            $raw = File::get($this->logsPath . '/' . $selected);
            $content = $raw;
            $lines = collect(explode("\n", $raw))
                ->filter()
                ->reverse()
                ->take(500)
                ->values();
        }

        $search = trim($request->query('q', ''));

        if ($search !== '') {
            $lines = $lines->filter(fn ($line) => stripos($line, $search) !== false)->values();
        }

        return view('admin.logs.index', [
            'files' => $files,
            'selected' => $selected,
            'lines' => $lines,
            'search' => $search,
        ]);
    }
}
