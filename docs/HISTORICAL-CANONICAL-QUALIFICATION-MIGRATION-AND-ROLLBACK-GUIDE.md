# Historical Canonical Qualification Migration & Rollback Guide

Migration avant : `014_historical_canonical_qualifications.sql`.

Rollback : `014_historical_canonical_qualifications.down.sql`. Il supprime uniquement la table `content_seo.historical_canonical_qualifications` et préserve les autres fondations Content/SEO.

Avant un rollback durable, sauvegarder les décisions. Exécuter ensuite le fichier `.down.sql` et vérifier que `to_regclass('content_seo.historical_canonical_qualifications')` retourne `NULL`. La migration avant recrée contraintes et index. Le cycle est validé sur PostgreSQL réel.
