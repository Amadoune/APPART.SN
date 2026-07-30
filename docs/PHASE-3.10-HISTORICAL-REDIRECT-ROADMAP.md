# Roadmap Phase 3.10 — Historical Canonical Redirect

## 3.10A — Historical Redirect Contract Foundation

Port `HistoricalRedirectResolver`, modèles immuables, sept résultats fermés, diagnostics typés, garde-fous d'architecture et documentation. Aucune infrastructure ni intégration Runtime/HTTP.

## 3.10B — Historical Redirect PostgreSQL Foundation

Fondation mise en œuvre après GO 3.10A : stratégie de stockage, migration réversible, mapper strict et adaptateur PostgreSQL en lecture bornée de décisions déjà matérialisées. La certification GO reste soumise à la validation PostgreSQL réelle et à la baseline complète. Aucun élargissement implicite vers HTTP ou Runtime.

## Étapes ultérieures soumises à GO dédiés

- 3.10C — Composition applicative et binding Runtime : mise en œuvre après GO 3.10B ; certification encore soumise à la baseline complète.
- 3.10CA — Fondation contractuelle de qualification Current/Historical/Unknown : mise en œuvre après le NO GO 3.10D ; certification soumise à la baseline complète.
- 3.10CB — Persistance PostgreSQL de qualification : mise en œuvre après GO 3.10CA ; certification soumise aux baselines PostgreSQL et applicative complètes.
- 3.10CC — Composition Runtime de qualification : mise en œuvre après GO 3.10CB ; certification soumise au Runtime Health et aux baselines complètes.
- 3.10D — Adaptateur HTTP consommant qualifier puis resolver sans déduction de destination : repris après GO 3.10CC, avec HTTP 301 explicite et mapping fail-safe exhaustif ; certification soumise aux baselines complètes.
- 3.10E — Certification bout en bout et observabilité : campagne finale exécutant la chaîne Laravel/PostgreSQL de production, sans nouveau composant ni logique métier. Son GO clôture la Phase 3.10.

Chaque étape doit préserver `PublicListingQuery` Current-only et la baseline certifiée Public Projection Runtime.
