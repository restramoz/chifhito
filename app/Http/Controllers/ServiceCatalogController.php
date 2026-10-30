<?php

namespace App\Http\Controllers;

use App\Services\SchemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class ServiceCatalogController extends Controller
{
    public function index(SchemeService $schemes): View
    {
        $categories = collect($schemes->all())
            ->groupBy(fn (array $program): string => $program['category_label'] ?? 'Layanan Lainnya')
            ->sortKeys();

        return view('catalog.index', [
            'categories' => $categories,
            'totalServices' => $categories->sum(fn (Collection $programs): int => $programs->count()),
        ]);
    }
}
