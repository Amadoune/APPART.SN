# Professional Status Outbox Owner Schema Specification

La migration additive 030 crée dans `professionals` les quatre structures Outbox génériques avec les mêmes colonnes et contraintes que l'owner historique : messages, deliveries, cursors et replays. Deux index génériques couvrent claim et ordre.

Le rollback supprime exclusivement ces quatre tables. L'Inbox 029 et les journaux Professional Status restent intacts.
