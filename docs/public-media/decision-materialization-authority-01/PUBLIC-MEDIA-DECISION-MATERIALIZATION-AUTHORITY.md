# Public Media Decision Materialization Authority 01

## Completion 01 — décision gouvernante

Le NO GO ci-dessous est conservé comme historique. Sa cause est fermée par Binary Delivery & URL Authority 01. Le contrat productif est V2 avec locator relatif `/media/{mediaId}/revisions/{assetVersion}`; aucune storageKey ni origine n'est persistée.

Owner final : Media pour les faits, Media Public Delivery pour locator/GET, Public Media pour sélection/ordre/primary, Public Projection consumer. L'autorité est complète et le sequencing est Binary Delivery Implementation puis Materialization Implementation.

## Décision

L'autorité métier des faits médias reste `Media`. `Public Media` est propriétaire de la décision publique dérivée et de son store. Listing fournit le contexte Published; Public Projection et ContentSeo sont consommateurs.

L'autorité ne peut pas être fermée : `PublicMediaItem` exige une URL publique valide alors que le seul fait binaire productif constaté est une `storageKey` privée. Aucun contrat owner ne transforme cette référence en URL publique durable.

## Conséquence

Le materializer, son trigger, son catch-up et son refresh restent non autorisés. Autorité préalable unique : **Public Media Binary Delivery & URL Authority 01**.

## Gouvernance

Aucun code, migration, write PostgreSQL, ActiveGeneration ou Projection n'a été exécuté.
