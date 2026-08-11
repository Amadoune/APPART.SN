<x-layouts.public title="Revue des publications — APPART.SN" robots="noindex, nofollow">
    <main class="review-shell">
        <section class="review-hero">
            <div class="shell-container review-hero__grid">
                <div><p class="shell-kicker">Publication Review</p><h1>Publier avec attention.</h1><p>Une file dédiée aux candidatures soumises. Chaque étape est autorisée, traçable et confirmée.</p></div>
                <aside class="review-security" aria-label="Sécurité de la revue"><span aria-hidden="true">✓</span><div><strong>Accès reviewer</strong><p>Les décisions sont liées à votre session IAM.</p></div></aside>
            </div>
        </section>

        <section class="shell-section review-workspace" aria-labelledby="review-title">
            <div class="shell-container">
                @if ($mode === 'queue')
                    <div class="review-heading"><div><p class="shell-kicker">Candidatures</p><h2 id="review-title" class="shell-heading">File de publication</h2></div></div>
                    @if ($result->status === \App\Application\PublicationReviewExperience\PublicationReviewExperienceStatus::Available)
                        <div class="review-list">
                            @foreach ($result->data['items'] as $item)
                                <article class="review-card"><div><span class="review-badge">Submitted</span><h3>Annonce {{ substr($item->listingId, 0, 8) }}</h3><p>Soumise le {{ $item->submittedAt->format('d/m/Y à H:i') }} · Révision {{ $item->submissionVersion }}</p></div><a class="shell-button shell-button--primary" href="{{ route('publication-review.show', $item->queueItemId) }}">Ouvrir la revue</a></article>
                            @endforeach
                        </div>
                    @else
                        <div class="review-state" role="status"><span aria-hidden="true">✓</span><h2>Aucune candidature en attente.</h2><p>La file est à jour. Les nouvelles annonces soumises apparaîtront ici.</p></div>
                    @endif
                @elseif ($mode === 'candidate')
                    @php($item = $result->data['item'])
                    <div class="review-panel"><p class="shell-kicker">Fiche de revue</p><h2 id="review-title">Annonce {{ substr($item->listingId, 0, 8) }}</h2><dl class="review-facts"><div><dt>État</dt><dd>Submitted</dd></div><div><dt>Révision</dt><dd>{{ $item->submissionVersion }}</dd></div><div><dt>Soumission</dt><dd>{{ $item->submittedAt->format('d/m/Y H:i') }}</dd></div></dl><form method="post" action="{{ route('publication-review.claim', $item->queueItemId) }}">@csrf<input type="hidden" name="commandId" value="{{ (string) \Illuminate\Support\Str::uuid() }}"><input type="hidden" name="expectedVersion" value="{{ $item->version }}"><button class="shell-button shell-button--primary" type="submit">Prendre en charge</button></form></div>
                @elseif ($mode === 'claimed')
                    @php($item = $result->data['item'])
                    <div class="review-panel"><span class="review-step">1 / 2</span><p class="shell-kicker">Candidature assignée</p><h2 id="review-title">Commencer la revue</h2><p>Cette candidature vous est maintenant réservée. Le démarrage applique exclusivement la transition certifiée BeginReview.</p><form method="post" action="{{ route('publication-review.begin', $item->queueItemId) }}">@csrf<input type="hidden" name="commandId" value="{{ (string) \Illuminate\Support\Str::uuid() }}"><input type="hidden" name="expectedVersion" value="{{ $item->version }}"><input type="hidden" name="listingId" value="{{ $item->listingId }}"><input type="hidden" name="submissionVersion" value="{{ $item->submissionVersion }}"><button class="shell-button shell-button--primary" type="submit">Begin Review</button></form></div>
                @elseif ($mode === 'reviewing')
                    <div class="review-panel"><span class="review-step">2 / 2</span><p class="shell-kicker">Under Review</p><h2 id="review-title">Approuver la publication</h2><p>La revue est ouverte. L'approbation publiera le Listing puis activera sa projection publique, sans écriture Search directe.</p><form method="post" action="{{ route('publication-review.approve', $queueItemId) }}">@csrf<input type="hidden" name="commandId" value="{{ (string) \Illuminate\Support\Str::uuid() }}"><input type="hidden" name="projectionCommandId" value="{{ (string) \Illuminate\Support\Str::uuid() }}"><input type="hidden" name="expectedVersion" value="{{ $result->data['version'] }}"><input type="hidden" name="listingId" value="{{ $listingId }}"><input type="hidden" name="submissionVersion" value="{{ $submissionVersion }}"><button class="shell-button shell-button--primary" type="submit">Approuver et publier</button></form></div>
                @else
                    <div class="review-state review-state--success" role="status"><span aria-hidden="true">✓</span><p class="shell-kicker">Publication confirmée</p><h2 id="review-title">L’annonce est maintenant publique.</h2><p>La projection a été activée. Elle est désormais disponible pour la recherche et la fiche publique.</p><div class="review-actions"><a class="shell-button shell-button--primary" href="{{ route('public-search.experience') }}">Voir la recherche</a><a class="shell-button shell-button--quiet" href="{{ route('publication-review.index') }}">Retour à la file</a></div></div>
                @endif
            </div>
        </section>
    </main>
</x-layouts.public>
