# Lead Lifecycle Event Transport Analysis

## Décision

Le transport encapsule l'événement 4.4E sous la seule clé `canonicalEvent`. Il n'extrait, n'enrichit et ne transforme aucun champ métier. Sa restauration stricte sert uniquement à vérifier la forme canonique, l'identité et la conservation byte-for-byte.

Le contrat Delivery commun est réutilisé sans introduire d'Outbox. `eventId` reste l'identité métier. `messageId` est l'identité technique déterministe de l'enveloppe et demeure distincte.

## Frontière

La fondation contient uniquement payload Delivery, métadonnées techniques, enveloppe V1, sérialiseur, port et résultats de routage. Elle ne contient ni routeur concret, Inbox, persistance, Consumer, Worker, publication, transaction ou HTTP.
