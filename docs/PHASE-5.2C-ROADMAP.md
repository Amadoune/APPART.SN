# Phase 5.2C — Professional Profile Roadmap

## Séquence proposée

1. **Discovery / Blueprint** — présent dossier, aucun artefact technique.
2. **Boundary Review** — ouverture éventuelle et certification de
   `A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01`.
3. **Contracts Foundation** — Commands, Queries, résultats fermés, ports,
   confidentialité et catalogues documentaires.
4. **Persistence Foundation** — stores propriétaires additifs, migrations
   postérieures à 060, optimistic locking et concurrence PostgreSQL.
5. **Runtime Foundation** — composition owner-scoped et availability
   fail-closed.
6. **Operations Foundation** — orchestration locale, vérification, mandats et
   handoffs publics, sans transaction cross-domain.
7. **Event / Transport / Delivery Foundation** — catalogue minimal,
   versionnement, replay, retry et quarantaine.
8. **Atomic Delivery / Outbox Foundation** — uniquement si des événements
   métiers sont retenus.
9. **HTTP & Public Profile Integration** — session IAM, auto-scope,
   autorisation et exposition publique minimale.
10. **Final Certification & Freeze**.

Chaque jalon exige une décision explicite d’autorité. Aucun jalon suivant n’est
ouvert par le GO du Discovery.

## Stratégie Persistence documentaire

- implémentation future du `ProfessionalRegistry` sans mutation de l’Aggregate ;
- tables owner-scoped séparées pour PublicProfile et Verification ;
- projection Portfolio propriétaire ;
- aucune FK cross-domain, cascade ou SQL dans une capacité gelée ;
- intents/checksums déterministes, optimistic locking et révisions append-only ;
- documents de vérification hors base métier, référencés de façon opaque ;
- migrations exclusivement additives après 060.

## Stratégie Runtime documentaire

- providers distincts Core, Profile, Verification et Portfolio ;
- composition publique unique ProfessionalProfileRuntimeV1 ;
- bindings lazy/singleton et connexion PostgreSQL partagée ;
- disponibilité pure, sans persistence ni transaction ;
- diagnostics fermés sans PII ;
- aucune modification du catalogue Runtime Health sans décision dédiée.

## Stratégie HTTP documentaire

- endpoints privés de gestion et endpoints publics de lecture séparés ;
- session IAM et auto-scope obligatoires pour les mutations ;
- idempotency key UUID, validation stricte, rate limiting HMAC et `no-store`
  pour les surfaces privées ;
- aucune exposition de preuve, claim privé ou diagnostic interne ;
- contrôleurs adaptateurs sans logique métier.

## Stratégie Event documentaire

Catalogue candidat, non autorisé à l’implémentation :

- profile published/hidden ;
- verification granted/rejected/expired/revoked ;
- establishment added/removed et mandate granted/revoked uniquement après
  décision de compatibilité avec les événements historiques existants.

Un événement n’est retenu que s’il possède un consumer démontré. Aucun événement
concret, transport ou Outbox n’est créé pendant Discovery.

## Gate Discovery

Recommandation : **GO**.

Justification :

- owners uniques et non duplicatifs ;
- frontières gelées explicites ;
- modèle additif exécutable ;
- risques et dépendances qualifiés ;
- réserve F-05 visible et amendable avant implémentation ;
- aucun changement technique introduit.
