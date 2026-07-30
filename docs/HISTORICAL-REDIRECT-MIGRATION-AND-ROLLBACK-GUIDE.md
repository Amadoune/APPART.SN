# Historical Redirect Migration and Rollback Guide

Migration avant : `013_historical_redirect_decisions.sql`.

Rollback : `013_historical_redirect_decisions.down.sql`. Il supprime uniquement `content_seo.historical_redirect_decisions`; le schéma Content/SEO et les autres tables restent intacts.

Procédure : arrêter toute écriture future vers cette tranche, sauvegarder les décisions si nécessaire, exécuter le fichier `.down.sql`, puis vérifier `to_regclass('content_seo.historical_redirect_decisions') IS NULL`. La réapplication du fichier avant recrée table, contraintes et index. Le test PostgreSQL dédié prouve ce cycle.

Le rollback détruit les décisions de la table et doit donc être précédé d'une sauvegarde en environnement durable.
