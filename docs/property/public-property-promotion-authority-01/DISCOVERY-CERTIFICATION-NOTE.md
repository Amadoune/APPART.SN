# Discovery Certification Note

## Verdict

**GO PROPOSÉ — DISCOVERY 01**

## Résultat certifiable

La Discovery établit sans ambiguïté :

- deux autorités persistantes distinctes, Property Authoring et Aggregate Property ;
- leurs owners et contrats respectifs ;
- la dépendance stricte de Projection envers `PropertyRegistry` et l'Aggregate Domain ;
- l'ensemble des adapters, événements, outbox/inbox et projections Property actuels ;
- l'absence de tout chemin `PropertyAuthoringState → RealEstateCatalog\Property` ;
- la première frontière manquante entre lecture Authoring et `RegisterProperty / PropertyRegistry::add` ;
- les responsabilités que toute future qualification devra préserver.

## Cause observée de RC2 Iteration 10

`CertifiedPublicListingProjectionSource → PropertyRegistry::find → null → PropertyMissing`.

Projection ne peut pas corriger ce manque : elle n'est ni owner d'Authoring ni owner des invariants Property.

## Gouvernance

Aucune implémentation, migration, route, Provider, binding, contrat PHP, événement ou correction n'a été créé. RC2 reste suspendue après l'itération 10. Aucune itération 11 n'est ouverte.

La seule suite autorisée par ce verdict est l'ouverture séparée de :

**PUBLIC PROPERTY PROMOTION AUTHORITY 01 — BLUEPRINT 01**

Le présent document ne préjuge d'aucune solution de ce Blueprint.
