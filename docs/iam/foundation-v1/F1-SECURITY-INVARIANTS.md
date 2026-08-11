# F1 — Security Invariants

## Invariants confirmés

1. fail-closed pour toute dépendance absente, incohérente ou corrompue ;
2. réponse publique homogène pour compte absent et credential rejeté ;
3. aucun plaintext credential ou secret Session persisté, journalisé ou exposé ;
4. aucune comparaison de hashes construits indépendamment ;
5. aucune lecture de repository depuis HTTP ;
6. aucune décision cryptographique dans Controller, commande locale ou SQL ;
7. ancien secret définitivement invalide après rotation ;
8. expiration idle et absolue toutes deux requises ;
9. session révoquée, expirée ou antérieure au checkpoint invalide ;
10. optimistic locking, idempotence et rollback externe préservés ;
11. `__Host-appart_session`, `Secure`, `HttpOnly`, `SameSite=Strict`, path `/`, aucun Domain ;
12. binding HTTP fail-closed conservé jusqu'à une F2 explicitement ouverte.

## Interdiction de matérialisation partielle

Un resolver ou verifier isolé ne peut être déclaré certifiable tant que la policy Session et les autorités cryptographiques ne sont pas définies. Cela créerait une chaîne dont certains verdicts seraient implicitement décidés par l'implémentation.
