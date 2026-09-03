# Source Identity

| Élément | Identité |
|---|---|
| HEAD | `6ec7d1ec9ecce0900cdb697b3581413816f747d8` |
| Tree | `68c051cedfb56bbe4e391f8499f1d1ce420249d9` |
| Parent / F22 migration amendment | `e0f76f6bc2e623125d301cd3c84ba53f3282b125` |
| R5 | `0067a77423c2ff16b02f35d85301d45024742a77` |
| R8 | `ebef23e12707d019eda7c1de799689ae470e1582` |
| R9 | `afa494648d082a6f85825a1ad05b80d712befc8f` |
| R10 | `145cd1c6d0280b4d67d4d2e0867b050f893f3466` |
| Worktree | `C:/laragon/www/APPART-R12-APP-URL-CORRECTION-01` |

Topologie : R5 est l'ancêtre commun. F22/`6ec7d1ec` forme une branche directe issue de R5. R6→R7→R8→R9→R10 forme une branche successor sœur. Ni F22 ni `6ec7d1ec` n'est ancêtre de R8/R9/R10, et les corrections R8 ne sont pas ancêtres de la source R12 actuelle.

État initial du diagnostic : `phpunit.xml` est l'unique modification source, limitée à l'insertion canonique `<env name="APP_URL" value="https://appart.test"/>`. Aucun tag R12 n'existe.
