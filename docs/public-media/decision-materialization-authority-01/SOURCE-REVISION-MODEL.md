# Source revision model

`MediaCollection::version()` capture ajout, retrait, archive, reorder, primary et caption. L'asset ingestion possède sa propre version et capture readiness/storage metadata.

Une révision Public Media doit aussi capturer la décision de delivery URL. Cette composante n'existe pas. Utiliser la seule version de collection omettrait une mutation d'URL/disponibilité publique; le modèle final est donc bloqué.
