<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireIdentityAccessSession;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IamWebEntryExperienceTest extends TestCase
{
    #[Test]
    public function public_home_exposes_real_login_and_authoring_entry_links(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('href="'.route('iam-web-entry.login').'"', false)
            ->assertSee('Se connecter')
            ->assertSee('Déposer une annonce');
    }

    #[Test]
    public function login_page_exposes_only_the_certified_login_inputs_and_csrf_context(): void
    {
        $response = $this->get('/connexion');

        $response->assertOk()
            ->assertSee('Ravi de vous revoir.')
            ->assertSee('name="csrf-token"', false)
            ->assertSee('data-iam-login-form', false)
            ->assertSee('name="identifier"', false)
            ->assertSee('name="credential"', false)
            ->assertDontSee('Créer un compte')
            ->assertDontSee('Mot de passe oublié');
    }

    #[Test]
    public function unauthenticated_workspace_access_remains_fail_closed(): void
    {
        $this->get('/authoring/workspace')
            ->assertUnauthorized()
            ->assertExactJson(['status' => 'authentication_required']);
    }

    #[Test]
    public function authenticated_workspace_exposes_only_the_certified_authoring_and_media_steps(): void
    {
        $this->withoutMiddleware(RequireIdentityAccessSession::class);

        $this->get('/authoring/workspace')
            ->assertOk()
            ->assertSee('Créer une annonce')
            ->assertSee('data-listing-wizard', false)
            ->assertSee('name="transactionKind"', false)
            ->assertSee('name="propertyType"', false)
            ->assertSee('name="city"', false)
            ->assertSee('name="neighborhood"', false)
            ->assertSee('name="images"', false)
            ->assertSee('Soumettre l’annonce')
            ->assertDontSee('Publier l’annonce');
    }
}
