<x-layouts.public title="Tableau de bord propriétaire — APPART.SN">
    <section class="dashboard-hero">
        <div class="shell-container dashboard-hero__grid">
            <div>
                <p class="shell-kicker">Espace propriétaire</p>
                <h1>Vos annonces, en un regard.</h1>
                <p>Une vue de lecture claire des annonces publiques disponibles. Le rattachement à votre compte sera activé avec la future intégration IAM.</p>
            </div>
            <aside class="dashboard-readonly" aria-label="Statut de cet espace">
                <span aria-hidden="true">◎</span>
                <div><strong>Mode lecture</strong><p>Aucune action ne modifie vos annonces.</p></div>
            </aside>
        </div>
    </section>

    <section class="shell-section dashboard" aria-labelledby="dashboard-title">
        <div class="shell-container">
            <div class="dashboard-heading">
                <div>
                    <p class="shell-kicker">Portefeuille public</p>
                    <h2 id="dashboard-title" class="shell-heading">Annonces visibles</h2>
                </div>
                @if ($dashboard->status === \App\Application\OwnerDashboard\OwnerDashboardReadStatus::Available)
                    <p class="dashboard-count">{{ count($dashboard->listings) }} {{ count($dashboard->listings) > 1 ? 'annonces' : 'annonce' }}</p>
                @endif
            </div>

            @if ($dashboard->status === \App\Application\OwnerDashboard\OwnerDashboardReadStatus::Available)
                <div class="dashboard-list">
                    @foreach ($dashboard->listings as $listing)
                        <article class="dashboard-card">
                            <a class="dashboard-card__media" href="{{ url($listing->canonicalPath) }}" aria-label="Ouvrir {{ $listing->title }}" @if ($listing->imageUrl !== null) style="background-image:url('{{ $listing->imageUrl }}')" @endif></a>
                            <div class="dashboard-card__content">
                                <div class="dashboard-card__badges">
                                    <span class="dashboard-badge dashboard-badge--success">{{ $listing->status === 'published' ? 'Publiée' : ucfirst($listing->status) }}</span>
                                    <span class="dashboard-badge">Visible publiquement</span>
                                </div>
                                <h3><a href="{{ url($listing->canonicalPath) }}">{{ $listing->title }}</a></h3>
                                <p class="dashboard-card__meta">
                                    {{ $listing->transaction === 'rent' ? 'Location' : ($listing->transaction === 'sale' ? 'Vente' : 'Transaction non qualifiée') }}
                                    <span aria-hidden="true">·</span> {{ ucfirst($listing->propertyType) }}
                                    @if ($listing->city !== null)<span aria-hidden="true">·</span> {{ $listing->city }}@endif
                                </p>
                                <dl class="dashboard-dates">
                                    <div><dt>Publication</dt><dd>{{ $listing->publishedAt->format('d/m/Y') }}</dd></div>
                                    <div><dt>Expiration</dt><dd>{{ $listing->expiresAt->format('d/m/Y') }}</dd></div>
                                </dl>
                            </div>
                            <div class="dashboard-card__actions">
                                <a class="shell-button shell-button--quiet" href="{{ url($listing->canonicalPath) }}">Voir la fiche</a>
                                <button class="shell-button" type="button" disabled title="Disponible avec la future intégration IAM">Gérer bientôt</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @elseif ($dashboard->status === \App\Application\OwnerDashboard\OwnerDashboardReadStatus::Empty)
                <div class="dashboard-state" role="status"><span aria-hidden="true">⌂</span><h2>Aucune annonce publique pour le moment.</h2><p>Votre portefeuille apparaîtra ici dès qu’une annonce publique sera disponible.</p></div>
            @else
                <div class="dashboard-state dashboard-state--error" role="alert"><h2>Le portefeuille est momentanément indisponible.</h2><p>Réessayez dans quelques instants. Aucun détail technique n’est exposé.</p></div>
            @endif
        </div>
    </section>
</x-layouts.public>
