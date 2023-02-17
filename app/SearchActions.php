<?php

declare(strict_types=1);

namespace Kami\Cocktail;

use Kami\Cocktail\Models\SiteSearchable;

class SearchActions
{
    public static function getPublicApiKey(): ?string
    {
        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\MeiliSearch\Client */
        $engine = app(\Laravel\Scout\EngineManager::class)->engine();

        $key = $engine->createKey([
            'actions' => ['search', 'documents.*', 'indexes.*', 'tasks.*', 'settings.*'],
            'indexes' => ['cocktails', 'ingredients', 'site_search_index'],
            'expiresAt' => null,
            'name' => 'Bar Assistant',
            'description' => 'Client key generated by Bar Assistant Server'
        ]);

        return $key->getKey();
    }

    public static function getPublicDemoApiKey(): ?string
    {
        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\MeiliSearch\Client */
        $engine = app(\Laravel\Scout\EngineManager::class)->engine();

        $key = $engine->createKey([
            'actions' => [
                'search'
            ],
            'indexes' => ['cocktails', 'ingredients', 'site_search_index'],
            'expiresAt' => null,
            'name' => 'Bar Assistant DEMO',
            'description' => 'Client key generated by Bar Assistant Server'
        ]);

        return $key->getKey();
    }

    public static function checkHealth(): ?array
    {
        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\MeiliSearch\Client */
        $engine = app(\Laravel\Scout\EngineManager::class)->engine();

        return $engine->health();
    }

    public static function updateIndexSettings(): void
    {
        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\MeiliSearch\Client */
        $engine = app(\Laravel\Scout\EngineManager::class)->engine();

        $engine->index('cocktails')->updateSettings([
            'filterableAttributes' => ['id', 'tags', 'user_id', 'glass', 'average_rating', 'main_ingredient_name', 'method', 'calculated_abv'],
            'sortableAttributes' => ['name', 'date', 'average_rating'],
            'searchableAttributes' => [
                'name',
                'tags',
                'description',
                'date',
            ]
        ]);

        $engine->index('cocktails')->updatePagination(['maxTotalHits' => 2000]);

        $engine->index('ingredients')->updateSettings([
            'filterableAttributes' => ['category', 'strength_abv', 'origin', 'color'],
            'sortableAttributes' => ['name', 'strength_abv'],
            'searchableAttributes' => [
                'name',
                'description',
                'category',
                'origin',
            ]
        ]);

        $engine->index('ingredients')->updatePagination(['maxTotalHits' => 2000]);
    }

    public static function updateSearchIndex(SiteSearchable $model): void
    {
        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\MeiliSearch\Client */
        $engine = app(\Laravel\Scout\EngineManager::class)->engine();

        $engine->index('site_search_index')->addDocuments([
            $model->toSiteSearchArray()
        ], 'key');
    }

    public static function deleteSearchIndex(SiteSearchable $model): void
    {
        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\MeiliSearch\Client */
        $engine = app(\Laravel\Scout\EngineManager::class)->engine();

        $engine->index('site_search_index')->deleteDocument($model->toSiteSearchArray()['key']);
    }

    public static function flushSearchIndex(): void
    {
        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\MeiliSearch\Client */
        $engine = app(\Laravel\Scout\EngineManager::class)->engine();

        $engine->index('site_search_index')->delete();
    }
}
