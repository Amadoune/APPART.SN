# APPART.TEST Local Product Bootstrap 01 — Problem Report

## Incidents rencontrés et résolution

### VirtualHost obsolète

- constat : `appart.test` pointait vers `C:/laragon/www/appart` ;
- impact : l'ancien produit était servi à la place d'APPART-REBUILD ;
- correction locale : `DocumentRoot` et bloc `Directory` alignés sur `C:/laragon/www/APPART-REBUILD/public` ;
- validation : Apache `Syntax OK`, vhost reconnu, réponse Laravel obtenue.

### Configuration Apache non rechargée

- constat : les processus Apache existants conservaient l'ancien VirtualHost ;
- correction : redémarrage ciblé des processus Apache Laragon ;
- validation : Laravel répond désormais sur `appart.test`.

### PostgreSQL arrêté

- constat : le service Windows PostgreSQL 18 était arrêté et son data directory n'est pas accessible à l'utilisateur courant ;
- correction : démarrage administratif du service `postgresql-x64-18` ;
- validation : service `Running`, port 5432 disponible, Laravel connecté à PostgreSQL 18.4.

## Risque résiduel

Apache a été lancé comme processus Laragon local et non comme service Windows. Après redémarrage de la machine, Laragon devra être démarré pour rendre `appart.test` accessible. PostgreSQL est configuré en démarrage automatique.

Aucun problème fonctionnel ou métier n'a été traité dans ce chantier.
