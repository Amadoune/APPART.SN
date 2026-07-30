# Listing Publication Outbox Compatibility Analysis

## Extension retenue

L'infrastructure Public Projection Delivery existante transporte désormais deux familles explicitement séparées : cinq demandes techniques historiques de reconstruction et quinze événements métier Listing Publication V1.

Le catalogue référence directement l'enum fermé 4.1EA. Le mapper reconnaît cette même enum et délègue la restauration au payload 4.1EBA. Aucune liste parallèle des quinze types n'est copiée dans l'infrastructure.

Le Consumer de transport restaure l'événement, vérifie la cohérence de l'enveloppe technique et appelle une fois le routeur durable 4.1EBR. Il ne connaît ni workflow, ni HTTP, ni Projection.

## Absence d'émission

L'orchestrateur 4.1D reste inchangé. Cette compatibilité rend l'Outbox capable de transporter les événements mais ne lui fournit encore aucun producteur.
