# Professional Status Context Transaction and Idempotence Invariants

La future persistance contextuelle devra :

- conserver journal historique et contexte dans une transaction PostgreSQL unique ;
- valider ou annuler intégralement les deux écritures ;
- garantir exactement un contexte par append versionné ;
- accepter un rejeu strictement identique sans nouvelle écriture ;
- refuser explicitement une divergence de contexte ;
- verrouiller les accès concurrents par identité Professional Status ;
- préserver le store historique et la migration 027 sans amendement ;
- fonctionner sous transaction locale et transaction externe.

Ces invariants sont contractuels. Leur implémentation appartient à un sprint ultérieur.
