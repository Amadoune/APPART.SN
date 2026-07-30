# Phase 5.1G — Event Catalog

Le catalogue est fermé à six événements. Authentication, attempts, sessions, recovery, claim reservation, contact pending/verified et profile revision restent privés.

Tout ajout de type ou de PII exige un contrat versionné ultérieur. Aucun événement Account Status V1 n'est modifié.

L'event ID dépend du type, de l'owner, du compte, de la version résultante, de la date métier, de correlation et de causation. Un retry avec les mêmes preuves reproduit la même identité.
