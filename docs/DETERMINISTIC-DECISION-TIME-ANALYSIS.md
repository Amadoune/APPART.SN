# Sprint 3.8G — Analyse Deterministic Decision Time

## Owner officiel

`decisionAt` appartient à la décision source Content/SEO. Le snapshot certifié en 3.8C conserve déjà la valeur originale dans `ContentSeoSourceDecision`, au même niveau que les sources Listing, Search, Property et l'historique canonical.

Ce choix est nécessaire : `decisionAt` qualifie le moment de la décision SEO, et non le moment où une projection est reconstruite, rejouée, lue ou basculée. Le Runtime, le Projection Store et le Rebuild ne sont donc pas propriétaires de cette donnée.

## Décision technique

Aucune nouvelle persistance n'est créée. Le payload `content_seo.public_source_snapshots` contient déjà `decision_at` et son checksum SHA-256 couvre le payload complet. Le writer 3.8C persiste donc déjà atomiquement la décision et sa date officielle.

Le port `DecisionTimeReader` expose une lecture spécialisée. L'adaptateur délègue au `ContentSeoSourceSnapshotReader` certifié puis mappe exhaustivement `Found`, `Missing` et `Corrupted`.

## Déterminisme

Le reader ne construit aucune date. Il restitue l'objet `DateTimeImmutable` extrait du snapshot vérifié. Un replay identique converge sans remplacement, une transaction annulée restaure le snapshot précédent et les transitions de générations n'affectent jamais la table propriétaire.

## Périmètre

Aucun Domain, Snapshot, Writer, SQL, Store, Updater, Delivery, Rebuild ou Runtime certifié n'est modifié. La fondation ne crée pas le futur `PublicListingProjectionSource`.
