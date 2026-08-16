# Compatibility report

La décision préserve Media, Media Ingestion, Listing Lifecycle, Public Projection et ContentSeo. Elle ajoute une boundary de delivery owner Media sans rendre le disk public.

Le contrat V2 est additif; un adapter maintient les consumers exigeant une URL absolue. Le store JSONB existant est réutilisé, sans migration. Host et backend restent configurables par environnement.

Public Geography reste fermé. ActiveGeneration et Projection restent suspendues/non exécutées.
