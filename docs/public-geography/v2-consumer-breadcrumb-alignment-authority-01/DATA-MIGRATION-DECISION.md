# Data migration decision

Les anciennes projections V1 restent historiques et immuables. Une nouvelle génération produit un read model V2.

Le payload `read_model` est binaire/JSON sérialisé dans le store, sans colonnes breadcrumb URL obligatoires. Aucun backfill ni migration de données.
