# Chemin de matérialisation

## Chemin disponible

Le writer productif existe, mais seuls :

- `CreateLocalFirstListing::materializePublicSources()` ;
- `CreateLocalPublicFactListing::materializeSources()`

construisent explicitement une `SearchDecision`. Ces commandes choisissent elles-mêmes identité, état Visible, rang, facettes et révisions pour une démonstration locale déterminée.

## Chemin manquant

Aucun événement `Published`, consumer, gateway ou orchestrateur applicatif générique ne transforme les faits publics certifiés en `SearchDecision`. Aucun appel à `SearchDecisionWriter` n'existe hors ces commandes et tests.

## Moment normatif

La décision doit être matérialisée après que les faits nécessaires sont stables — au plus tôt après Published — et avant `ProjectPublishedListingV1`. Elle ne peut être produite après Projection puisque la Projection exige déjà sa version.

## Owner d'écriture

SearchDiscovery doit calculer et écrire sa décision dans une transaction owner-locale. PublicationReview peut orchestrer un handoff ou attendre un résultat fermé, mais ne doit ni choisir rank/facettes ni écrire la table Search.
