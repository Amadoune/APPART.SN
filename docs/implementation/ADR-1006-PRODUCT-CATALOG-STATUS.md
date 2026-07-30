# ADR-1006 — Product est un catalogue administré

## Statut

**Accepté et définitif — 17 juillet 2026**

## Contexte

Le modèle `Product` est identifié parmi les quatorze Roots de la Foundation, mais il diffère des treize Aggregates mutables :

- sa création `define` fixe toutes ses propriétés ;
- sa reconstitution exige strictement la version 1 et un unique historique `defined` ;
- aucune méthode de mutation n’existe ;
- aucun événement de domaine propre n’est produit ;
- l’Application dépend uniquement de `ProductCatalog::find(ProductId)` ;
- `CreateOrder` copie prix, bénéfice et durée dans `OrderLine`, protégeant la commande de toute évolution ultérieure du catalogue.

Deux options étaient autorisées : A, Aggregate mutable ; B, catalogue administré.

## Décision

**Option B — Catalogue administré.**

`Product` est une entrée immuable publiée dans un catalogue appartenant à MonetizationPayments. Le produit courant le consulte par `ProductCatalog`; il n’existe pas de `ProductRegistry`, de sauvegarde conditionnelle, de Repository d’Aggregate mutable ou d’événement Product à introduire pour le premier cycle PostgreSQL.

L’administration du catalogue est une capacité contrôlée distincte du flux transactionnel des commandes et paiements. Sa future définition opérationnelle devra préserver l’immuabilité : une modification commerciale crée une nouvelle identité de catalogue plutôt que de réécrire une entrée déjà référencée. Un retrait empêche de nouvelles commandes mais conserve l’entrée pour l’interprétation des historiques.

## Conséquences

- `ProductId` est réservé définitivement lors de la publication administrative.
- Le nom n’est pas une Business Key normative.
- Les OrderLine restent des snapshots autonomes de l’offre achetée.
- Le premier Repository PostgreSQL doit cibler un des treize Registries mutables, pas Product.
- Le futur adapter de `ProductCatalog` est un adapter de lecture de catalogue, pas un Repository au sens du Repository Protocol.
- Les tests communs des Registries ne s’appliquent pas à Product ; un contrat de lecture Catalogue vérifie absence, lecture détachée/immuable et fidélité des données.

## Options rejetées

### A — Aggregate mutable

Rejetée car elle nécessiterait de nouveaux comportements, événements, versions et un port d’écriture absents du modèle validé. Elle créerait aussi un risque de réinterprétation rétroactive des commandes.

## Réversibilité

La décision est définitive pour cette architecture. Une évolution vers un catalogue versionné ne modifierait jamais une entrée existante : elle introduirait un nouveau concept et une nouvelle décision d’architecture, sans transformer rétroactivement Product en Aggregate mutable.
