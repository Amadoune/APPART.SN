# Boundary Audit — Sprint 2.2

## Périmètre et méthode

Audit statique exhaustif des fichiers PHP sous `src`, `app` et `tests/Architecture`, inventaire des namespaces/imports et exécution de la suite Architecture. Les dépendances sont déterminées depuis les références `Appart\Modules\...`, les marqueurs Laravel/Eloquent/base/SQL et les déclarations de classes.

## Résultats

| Frontière | État | Preuve |
|---|---|---|
| Laravel dans Domain | conforme | zéro `Illuminate\`, `Laravel\` ou `App\` ; garde-fou automatisé |
| Domain → Infrastructure | conforme | aucun namespace/import Infrastructure |
| Application → Infrastructure (inverse) | conforme | aucun import ; garde-fou automatisé |
| Infrastructure → Use Cases concrets | conforme par absence | aucune couche Infrastructure ; garde-fou prêt |
| Couplage inter-module direct | conforme | zéro référence d’un module vers un autre |
| Dépendance circulaire | conforme | graphe inter-module vide, donc aucun cycle |
| Aggregate partagé | conforme | chaque Root/type réside dans un seul module ; identifiants homonymes locaux |
| Repository concret | conforme | aucun type concret suffixé `Repository` |
| Accès base / SQL | conforme | aucun DB facade, PDO, driver, DBAL ou instruction SQL dans `src`/`app` |
| Eloquent | conforme | aucun import Eloquent ni extension de `Model` |
| Controller métier | conforme | aucun Controller ajouté aux modules ou à la périphérie métier |
| Service Provider métier | conforme | aucun provider métier/infrastructure ajouté |

## Direction réelle

Dans chaque module implémenté : `Application/UseCase → Application/Contract + Domain`; le Domain reste autonome. Les besoins externes sont exprimés par des Catalogs/Registries locaux, jamais par import du module fournisseur. Geography possède en plus `Domain/Contract/PlaceLookup`, étendu par son Registry applicatif.

## Écarts détectés

1. `LegacyMigration` est une enveloppe vide, sans garde métier ni tests. Ce n’est pas une violation, mais 13 domaines physiques ne signifient que 12 modules implémentés.
2. `Product` est reconstructible et versionné sans Registry mutable ni événement propre. Son ownership d’écriture doit être décidé avant persistance.
3. `PaymentCatalog` et `RefundTransaction` n’ont pas de fake dédié. Des scénarios passent par les doubles agrégés existants, mais aucun double autonome ne matérialise ces deux contrats.
4. Certaines réservations uniques (email/téléphone, PropertyReference, PlaceCode/aliases, ListingId de projection) restent implicites. Le protocole doit être explicité avant Repository.
5. Les contrôles SQL ciblent le code PHP de production. Le Sprint 2.2 ne crée aucun emplacement technique ; les futurs emplacements autorisés devront recevoir leurs règles adaptées.

## Conclusion

Les frontières DDD actuelles sont propres, sans violation détectée. Le risque résiduel est décisionnel : persistance de `Product` et liste exacte des réservations d’unicité.
