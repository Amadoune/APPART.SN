<?php

namespace Tests\Feature;

use App\Application\OwnerDashboard\Contract\OwnerDashboardReadSourceV1;
use App\Application\OwnerDashboard\OwnerDashboardListing;
use App\Application\OwnerDashboard\OwnerDashboardReadResult;
use DateTimeImmutable;
use Tests\TestCase;

final class OwnerDashboardReadExperienceTest extends TestCase
{
    public function test_dashboard_renders_real_read_model_fields_and_no_active_business_action(): void
    {
        $this->app->instance(OwnerDashboardReadSourceV1::class, new class implements OwnerDashboardReadSourceV1
        {
            public function read(): OwnerDashboardReadResult
            {
                return OwnerDashboardReadResult::available([new OwnerDashboardListing(
                    'annonces/appartement-moderne-dakar',
                    'Appartement moderne à Dakar',
                    'https://media.appart.sn/listings/primary.webp',
                    'sale',
                    'apartment',
                    'Dakar',
                    'published',
                    'public',
                    new DateTimeImmutable('2026-07-18T10:01:00+00:00'),
                    new DateTimeImmutable('2026-11-15T10:00:00+00:00'),
                )]);
            }
        });

        $this->get('/espace-proprietaire')
            ->assertOk()
            ->assertSee('Vos annonces, en un regard.')
            ->assertSee('Appartement moderne à Dakar')
            ->assertSee('Dakar')
            ->assertSee('18/07/2026')
            ->assertSee('15/11/2026')
            ->assertSee('Gérer bientôt')
            ->assertSee('disabled', false);
    }

    public function test_dashboard_has_an_explicit_empty_state(): void
    {
        $source = $this->createMock(OwnerDashboardReadSourceV1::class);
        $source->method('read')->willReturn(OwnerDashboardReadResult::empty());
        $this->app->instance(OwnerDashboardReadSourceV1::class, $source);
        $this->get('/espace-proprietaire')->assertOk()->assertSee('Aucune annonce publique pour le moment.');
    }

    public function test_dashboard_has_an_explicit_unavailable_state(): void
    {
        $source = $this->createMock(OwnerDashboardReadSourceV1::class);
        $source->method('read')->willReturn(OwnerDashboardReadResult::unavailable());
        $this->app->instance(OwnerDashboardReadSourceV1::class, $source);
        $this->get('/espace-proprietaire')->assertOk()->assertSee('momentanément indisponible');
    }
}
