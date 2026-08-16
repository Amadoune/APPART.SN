# Preuve de provisioning du principal Authoring local

## Environnement

- application : APPART.SN ;
- environnement Laravel : `local` ;
- URL : `appart.test` / HTTPS qualifié ;
- base : PostgreSQL 18.4 locale, connexion `pgsql`, base `appart_test`, hôte `127.0.0.1`.

## Principal créé

- AccountId : `a2110000-0000-4000-8000-000000000001` ;
- identifiant : `rc2.owner.20260814@appart.test` ;
- usage : démonstration locale RC2 uniquement ;
- état : actif, non suspendu ;
- version initiale : 0 ;
- rôles : aucun.

## Chaîne certifiée exécutée

`CredentialHashAuthorityV1 → Argon2IdCredentialHashAuthority → RegisterAccount → AccountRegistry → PostgreSqlAccountRepository`

Le compte a été créé par `RegisterAccount`; le use case a appelé l’`AccountRegistry` productif. Aucun Repository n’a été appelé directement par la procédure de provisioning et aucun SQL IAM direct n’a été utilisé.

Le principal reviewer n’a été ni lu comme identité de substitution, ni modifié, ni utilisé pour l’Authoring.
