<?php

namespace App\Http\Controllers;

use App\Services\SchemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class ServiceCatalogController extends Controller
{
    public function index(SchemeService $schemes): View
    {
        $allPrograms = collect($schemes->trainings());
        $query = trim((string) request('q'));
        $category = (string) request('category', '');
        $categoryCounts = $allPrograms
            ->countBy('category_label')
            ->all();

        $programs = $allPrograms;
        if ($query !== '') {
            $programs = $programs->filter(fn (array $program): bool => stripos($program['title'] . ' ' . $program['category_label'], $query) !== false);
        }
        if ($category !== '') {
            $programs = $programs->where('category_label', $category);
        }

        $categories = $programs
            ->groupBy(fn (array $program): string => $program['category_label'] ?? 'Layanan Lainnya');

        return view('catalog.index', [
            'categories' => $categories,
            'totalServices' => $categories->sum(fn (Collection $programs): int => $programs->count()),
            'availableTrainingCount' => $allPrograms->count(),
            'categoryCounts' => $categoryCounts,
            'trainingCategories' => collect($schemes->trainingCategories())->keys(),
            'selectedCategory' => $category,
            'searchQuery' => $query,
            'hasActiveFilters' => $query !== '' || $category !== '',
        ]);
    }
}
