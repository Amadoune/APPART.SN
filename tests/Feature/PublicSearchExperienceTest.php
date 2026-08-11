<?php

namespace Tests\Feature;

use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Application\PublicSearchResults\PublicSearchListingSummary;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsResult;
use Tests\TestCase;

final class PublicSearchExperienceTest extends TestCase
{
    public function test_home_exposes_a_real_accessible_get_search_form(): void
    {
        $this->app->instance(PublicSearchResultsReaderV1::class, $this->reader(PublicSearchResultsResult::empty()));

        $this->get('/')
            ->assertOk()
            ->assertSee('action="'.route('public-search.experience').'"', false)
            ->assertSee('name="transaction"', false)
            ->assertSee('value="sale"', false)
            ->assertSee('value="rent"', false)
            ->assertSee('name="city"', false)
            ->assertSee('name="propertyType"', false)
            ->assertSee('type="submit"', false);
    }

    public function test_results_page_maps_filters_and_renders_public_listing_and_cursor(): void
    {
        $reader = new class implements PublicSearchResultsReaderV1
        {
            public ?PublicSearchResultsQuery $query = null;

            public function read(PublicSearchResultsQuery $query): PublicSearchResultsResult
            {
                $this->query = $query;

                return PublicSearchResultsResult::available([
                    new PublicSearchListingSummary('annonces/p03-appartement-a-vendre-dakar', 'listing-p03', 'Appartement à vendre à Dakar', 'apartment', '/p03.svg', 'sale', 'Dakar', 86, 3),
                ], 'annonces/p03-appartement-a-vendre-dakar');
            }
        };
        $this->app->instance(PublicSearchResultsReaderV1::class, $reader);

        $this->get('/recherche?transaction=sale&city=Dakar&propertyType=apartment')
            ->assertOk()
            ->assertSee('Appartement à vendre à Dakar')
            ->assertSee('À acheter')
            ->assertSee('Dakar')
            ->assertSee('Appartement')
            ->assertSee('86 m²')
            ->assertSee('3 pièces')
            ->assertSee('Prix non communiqué')
            ->assertSee('annonces/p03-appartement-a-vendre-dakar', false)
            ->assertSee('Voir plus de résultats');

        self::assertNotNull($reader->query);
        self::assertSame('sale', $reader->query->transaction);
        self::assertSame('Dakar', $reader->query->city);
        self::assertSame('apartment', $reader->query->propertyType);
    }

    public function test_empty_state_is_clear_and_actionable(): void
    {
        $this->app->instance(PublicSearchResultsReaderV1::class, $this->reader(PublicSearchResultsResult::empty()));

        $this->get('/recherche?transaction=rent&city=Dakar&propertyType=apartment')
            ->assertOk()
            ->assertSee('Aucun bien ne correspond actuellement à votre recherche.')
            ->assertSee('Essayez une autre ville ou modifiez vos critères.')
            ->assertSee('Modifier les critères');
    }

    public function test_technical_state_hides_internal_details(): void
    {
        $this->app->instance(PublicSearchResultsReaderV1::class, $this->reader(PublicSearchResultsResult::dependencyUnavailable()));

        $this->get('/recherche?transaction=sale&city=Dakar&propertyType=apartment')
            ->assertOk()
            ->assertSee('La recherche est momentanément indisponible.')
            ->assertDontSee('dependency_unavailable');
    }

    public function test_unknown_or_invalid_filters_are_never_silently_ignored(): void
    {
        $this->from('/')->get('/recherche?budget=100000')->assertRedirect('/')->assertSessionHasErrors('_request');
        $this->from('/')->get('/recherche?transaction=buy')->assertRedirect('/')->assertSessionHasErrors('transaction');
    }

    private function reader(PublicSearchResultsResult $result): PublicSearchResultsReaderV1
    {
        return new class($result) implements PublicSearchResultsReaderV1
        {
            public function __construct(private readonly PublicSearchResultsResult $result) {}

            public function read(PublicSearchResultsQuery $query): PublicSearchResultsResult
            {
                return $this->result;
            }
        };
    }
}
