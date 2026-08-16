# Contract versioning

Une V2 additive est nécessaire : `PublicMediaItemV2(mediaId, publicLocator, deliveryRevision, variants)`.

Le payload JSONB existant peut évoluer par version de contrat sans nouvelle table. Le reader V2 est canonique; un adapter V1 dérive `url` depuis l'origine validée. Aucun rewrite silencieux d'un payload V1 divergent.
