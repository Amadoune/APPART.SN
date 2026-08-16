# Security Boundary

Garanties obligatoires :

- middleware IAM sur Portfolio, Resume et Media ;
- AccountId exclusivement issu de la session ;
- ownership et délégations vérifiés server-side ;
- UUID client traité comme sélecteur non fiable ;
- absence et forbidden fusionnés vers 404 ;
- aucune donnée métier autoritative dans localStorage/sessionStorage ;
- aucune valeur de cookie, session ou credential exposée ;
- réponses privées `no-store` ;
- aucune lecture Projection/Search ;
- aucune URL Media publique avant publication.

Le bootstrap HTML doit être encodé comme JSON sûr ou obtenu par fetch same-origin avec CSRF non requis pour GET. Toute lecture binaire Media doit vérifier owner/property/media et refuser les chemins de stockage fournis par le client.
