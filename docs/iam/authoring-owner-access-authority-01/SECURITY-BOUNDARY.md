# Frontière de sécurité

Invariants maintenus :

- credential clair jamais persisté dans le repository ou la documentation ;
- hash produit uniquement par CredentialHashAuthorityV1 ;
- aucun SQL IAM direct ;
- aucun cookie ou contexte Session injecté ;
- AccountId exclusivement issu de la session ;
- aucun owner fourni par le client ;
- aucun rôle ou capability implicite ;
- contrôles owner/delegation conservés dans les Runtimes propriétaires ;
- reviewer existant strictement inchangé.
