# Public Geography Decision Materialization Authority 01

## Décision

Public Geography est une décision Geography owner consommée en lecture seule par Public Projection. Son store, son contrat et sa politique de promotion existent.

## Fail-fast

Le pipeline ne peut pas être entièrement autorisé : aucune autorité existante ne définit la représentation publique canonique d'un Place, notamment les URL du breadcrumb et la séquence source couvrant les mutations de hiérarchie. Les exemples P02/tests ne sont pas normatifs et divergent sur l'URL Home.

## Verdict

**NO GO PROPOSÉ**. Cause unique : absence d'une autorité canonique `Place → Public Geography representation/revision`.

## Reopening / Completion 01

La cause historique ci-dessus est fermée par `PUBLIC GEOGRAPHY CANONICAL PLACE REPRESENTATION AUTHORITY 01` (GO). V2 sans URL, identité Place, vecteur et chemin initial sont qualifiés.

Le Completion reste **NO GO** pour une nouvelle cause unique : le refresh descendant sur toute mutation Geography publique n'a ni transport complet (`PlaceRenamed` absent) ni lookup des terminaux affectés. Voir `AUTHORITY-COMPLETION-01.md`. Le NO GO historique demeure conservé.

## Reopening / Completion 02

La cause Completion 01 est fermée par Descendant Decision Refresh Authority (GO). Tous les modèles source sont qualifiés.

Completion 02 reste **NO GO** : les consumers certifiés exigent une URL (`ContentSeo\BreadcrumbItem`, projection `{label,url}`, Blade `<a href>`), tandis que V2 interdit une URL Geography et que l'Implementation mandate interdit ContentSeo changes. Cause unique et gate : `PUBLIC GEOGRAPHY V2 CONSUMER BREADCRUMB ALIGNMENT AUTHORITY 01`.

## Reopening / Completion 03

La cause Completion 02 est fermée par V2 Consumer Breadcrumb Alignment Authority et Implementation 01 (GO). V1 historique et V2 productif coexistent; les consumers exécutables acceptent V2 sans URL.

Les facts RC2 productifs ont été relus : Senegal → Dakar Region → Dakar, terminal `c3120000-0000-4000-8000-000000000003`, vecteur `[1,1,1]`, watermark 3. Store JSONB, writer monotone, chemins initial/catch-up/refresh, mutations et transport sont entièrement qualifiés. Les adaptations writer/transport restant à coder appartiennent au périmètre final de l'Implementation et ne requièrent aucune nouvelle authority.

**GO PROPOSÉ — Completion 03. Implementation 01 peut être ouverte.**
