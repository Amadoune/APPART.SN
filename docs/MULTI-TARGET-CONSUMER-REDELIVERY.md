# Reprise et redelivery multi-cibles

Le message Property ou Media reste l'unique unité durable. Une interruption avant la dernière page
empêche sa validation. La redelivery reparcourt les pages depuis le début :

1. les cibles déjà appliquées rendent `AlreadyApplied` ;
2. les cibles restantes sont exécutées dans le même ordre ;
3. le succès n'est rendu qu'après la dernière page ;
4. aucun message enfant, coordinateur ou état de progression n'est créé.

Cette stratégie préserve at-least-once sans double effet logique et reste compatible avec le Worker,
les leases, retries et quarantaines de 3.6C.
