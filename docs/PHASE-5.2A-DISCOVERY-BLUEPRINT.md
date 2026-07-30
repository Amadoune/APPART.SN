# Phase 5.2A — Property & Listing Authoring — Discovery / Blueprint

## 1. Statut

Discovery / Blueprint est GO CERTIFIÉ et FERMÉ. Le dossier appartient à la
baseline gelée de Phase 5.2A.

## 2. Problème produit

Le dépôt sait déjà enregistrer un `Property`, créer un `Listing` et exécuter
leurs lifecycles certifiés. Il ne fournit pas encore un parcours complet
d'annonceur : ownership, saisie progressive, contenu commercial, complétude,
portefeuille privé, délégation et soumission contrôlée vers le lifecycle.

## 3. État réel constaté

- `RealEstateCatalog/Property` possède les faits physiques, l'adresse, la
  référence, l'archivage et l'optimistic locking.
- `ListingLifecycle/Listing` possède l'identité, la référence Property, les
  révisions et le statut de publication.
- F-01 protège la chaîne Listing Publication Lifecycle.
- F-02 protège la chaîne Property Lifecycle.
- F-11 protège la Public Listing Projection.
- F-15 protège la fondation Delivery/Outbox générique.
- F-16 protège les migrations 001–043.
- F-17 et F-18 protègent IAM et les migrations 044–054.
- les stores actuels ne portent ni owner annonceur, ni contenu éditorial
  complet, ni état de complétude d'un brouillon.

## 4. Capacité cible

5.2A ajoute quatre autorités :

1. `PropertyAuthoring` : ownership et commande des faits Property existants ;
2. `ListingAuthoringDraft` : contenu privé, progressif et non publié ;
3. `ListingOwnership` : titulaire, délégations et politique d'accès ;
4. `AuthoringPortfolio` : projection privée reconstruisible des biens et
   annonces accessibles à un acteur.

`Property` et `Listing` restent les autorités de leurs identités et états.
Le nouveau modèle ne duplique jamais leurs statuts.

## 5. Parcours

1. un Account disponible initie un Property ;
2. l'owner est réservé atomiquement dans `PropertyAuthoring` ;
3. les faits physiques sont appliqués par les commandes publiques de
   RealEstateCatalog ;
4. un Listing et son `ListingAuthoringDraft` sont créés ;
5. le contenu est modifié avec version attendue et clé d'idempotence ;
6. la complétude est calculée à partir de vues owner-scoped ;
7. la soumission appelle la commande certifiée du lifecycle Listing ;
8. le portefeuille reflète les résultats sans devenir autorité d'écriture.

## 6. Hors périmètre

- ingestion et traitement binaire Media, réservés à 5.2B ;
- profil professionnel, mandat et équipe, réservés à 5.2C ;
- changement des lifecycles F-01/F-02 ;
- enrichissement de la projection publique F-11 ;
- modération, paiement, favoris, leads et réservation ;
- anonymisation ou effacement IAM ;
- toute modification Runtime Health.

## 7. Gates avant implémentation

- contrats fermés des quatre autorités ;
- décision sur la commande de création de Listing sans modification de F-01 ;
- schéma additif postérieur à 054 ;
- contrat minimal de lecture IAM Availability ;
- politique de délégation ne dépendant pas encore de Professional Profile ;
- preuve qu'aucune donnée privée d'authoring n'entre dans les événements
  publics ou la projection publique ;
- catalogue d'événements authoring distinct des catalogues lifecycle gelés.

## 8. Décision certifiée

Le modèle est compatible avec la baseline si toutes les extensions restent
additives. Tout besoin de modifier F-01, F-02, F-11, F-14 à F-18 déclenche un
amendement versionné avant code.
