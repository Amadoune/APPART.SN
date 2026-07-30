# Contract Test Blueprint

## 1. Statut et objectif

- **Phase :** 2
- **Sprint :** 2.3 — Repository Contract Foundation
- **Version :** 1.0
- **Statut :** normatif pour la construction des suites de contrat

Ce blueprint définit une seule suite comportementale par port, exécutable sans duplication contre le Fake de référence puis contre tout futur Repository PostgreSQL. Il ne crée aucun Repository, schéma, SQL, Unit of Work, Outbox ou Dispatcher.

## 2. Architecture de la suite

Chaque contrat possède :

1. une classe abstraite de scénarios, contenant toutes les assertions ;
2. une interface de harness propre au Registry, responsable de créer le système testé et ses fixtures ;
3. une implémentation de harness Fake ;
4. une future implémentation de harness PostgreSQL ;
5. une classe d’entrée minimale par backend, héritant de la même suite et fournissant uniquement le harness.

La classe d’entrée ne redéfinit aucun scénario. Toute différence de résultat entre Fake et PostgreSQL est un défaut du backend ou du contrat, jamais une raison de copier ou conditionner un test.

## 3. Contrat du harness

Le harness fournit conceptuellement :

- `freshRegistry()` : instance isolée et vide ;
- fabriques d’Aggregate minimal, mutable, terminal et à version non triviale ;
- identités et Business Keys distinctes ou conflictuelles ;
- lecture d’observation uniquement par le port public ;
- déclenchement déterministe d’un échec d’écriture avant validation ;
- remise à zéro complète entre scénarios ;
- pour PostgreSQL seulement, deux contextes indépendants et une barrière contrôlée de concurrence.

Les tests ne connaissent ni tableau interne du Fake, ni ORM, ni connexion, ni structure persistante. Un hook spécifique au backend peut préparer/vider l’environnement, mais ne change jamais les assertions métier.

## 4. Suite commune minimale pour les treize Registries mutables

Chaque Registry exécute sans duplication :

1. identité inconnue → absence explicite ;
2. ajout puis lecture fidèle et détachée ;
3. Aggregate rechargé sans événements résiduels ;
4. deux lectures produisent des instances indépendantes ;
5. conflit d’AggregateId traduit vers l’exception du port ;
6. conflit de chaque Business Key et identité enfant globale ;
7. sauvegarde avec version attendue ;
8. sauvegarde avec version périmée refusée ;
9. Aggregate absent lors de `save` traité comme conflit concurrent contractuel ;
10. version durable égale à celle du Root, jamais incrémentée par le Registry ;
11. rollback : aucune mutation ni réservation nouvelle visible ;
12. historique, collections, preuves et état terminal préservés ;
13. événements de l’instance appelante non effacés avant succès et non rejoués après rechargement ;
14. réutilisation/refus conforme à `RESERVATION-STRATEGY.md` ;
15. ownership stable d’une réservation ;
16. ordre de tests indifférent et état initial vide.

## 5. Profils spécialisés

| Profil | Ports concernés | Scénarios additionnels obligatoires |
|---|---|---|
| Identité seule | AdministrativeActionRegistry, ListingRegistry, OrderRegistry | collision d’ID, terminal conservé, rollback |
| Business Keys permanentes | PlaceRegistry, AccountRegistry, PropertyRegistry | collision de chaque clé, état terminal ne libérant rien |
| Réservation enfant | MediaCollectionRegistry, ModerationCaseRegistry, ProfessionalRegistry | opération `saveWith…Reservation`, conflit entre Roots, même owner stable, rollback combiné |
| Déduplication temporelle | LeadRegistry | conflit dans fenêtre, succès hors fenêtre, bornes temporelles, rollback du claim |
| Canonicals historiques | SeoProjectionRegistry | ListingId unique, canonical initial, changement atomic, historique jamais réutilisé |
| Projection multi-identités | SearchIndexRegistry | SearchIndexId, ListingId et SearchDocumentId atomiques ; `findByListing` fidèle |
| Idempotence paiement | PaymentRegistry | création, rejeu même intention, conflit autre intention, PaymentId concurrent, référence permanente |
| Transaction multi-Roots | RefundTransaction | Payment et Order validés ensemble, une version périmée annule les deux, aucun état partiel |

## 6. ProductCatalog

Conformément à ADR-1006, Product n’utilise pas la suite Registry. Une suite de lecture partagée `ProductCatalogContract` vérifie : identité absente, fidélité de l’entrée, immuabilité/détachement du résultat, snapshot commercial utilisé par la création de commande et conservation d’une entrée retirée pour les lectures historiques. Le mécanisme administratif de publication est hors du premier Repository.

## 7. Concurrence

Les scénarios séquentiels communs s’exécutent sur Fake et PostgreSQL. Les preuves de concurrence réelle s’ajoutent dans la même définition de contrat via une capacité `ConcurrentHarness`, activée uniquement lorsque le backend fournit deux unités d’exécution indépendantes. Elles contrôlent : ajout concurrent du même ID, réservation concurrente de la même clé, sauvegardes avec même version et un seul gagnant.

Le Fake doit conserver des tests déterministes de conflit, mais ne prétend pas prouver la simultanéité. L’absence de capacité concurrente n’autorise jamais un Repository PostgreSQL à éviter ces scénarios.

## 8. Données et déterminisme

- identités, dates et corrélations sont fixes et explicites ;
- aucune horloge système, hasard, réseau ou ordre global de suite ;
- chaque scénario possède ses données et nettoie son environnement ;
- aucune relance automatique d’un test instable ;
- les messages techniques ne sont pas assertés, seules les catégories contractuelles le sont ;
- secrets et données personnelles sont synthétiques et minimaux.

## 9. Ordre d’implémentation

1. Définir le noyau commun et le harness Fake.
2. Brancher les treize Fakes actuels et corriger toute divergence révélée.
3. Ajouter les profils de réservation spécialisés.
4. Choisir un premier Registry à identité seule pour la tranche PostgreSQL.
5. Faire passer exactement la même suite sur le Repository, puis ajouter mapping et concurrence réelle.

## 10. Porte d’acceptation d’un Repository

Un Repository n’est recevable que si sa suite commune, son profil spécialisé, ses tests de mapping, rollback et concurrence réelle sont verts. Les scénarios Fake et PostgreSQL doivent provenir des mêmes méthodes de test ; seules les fabriques de harness diffèrent.
