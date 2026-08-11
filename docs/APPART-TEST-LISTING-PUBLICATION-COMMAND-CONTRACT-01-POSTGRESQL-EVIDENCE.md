# APPART.TEST LISTING PUBLICATION COMMAND CONTRACT 01 — PostgreSQL Evidence

La composition transactionnelle reste techniquement plausible : workflow, Outbox et Listing Registry sont configurés autour de la même connexion PDO et leurs repositories préservent une transaction externe.

Cette propriété technique ne fournit aucune des autorités métier manquantes. Aucun scénario PostgreSQL de composition n'a donc été créé ni exécuté artificiellement.

Aucune table, migration, donnée ou snapshot n'a été modifié.
