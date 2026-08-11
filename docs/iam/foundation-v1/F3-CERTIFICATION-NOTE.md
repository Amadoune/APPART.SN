# F3 — Certification Note

## Verdict

**NO GO PROPOSÉ — APPART.SN IAM FOUNDATION v1 — F3 LOCAL PRINCIPAL PROVISIONING & HTTPS SESSION PROOF**

Le provisioning du principal est certifiable : compte réel, hash F1, `RegisterAccount`, `AccountRegistry`, aucune écriture SQL directe.

La preuve navigateur terminale est impossible dans le périmètre autorisé. APPART.TEST ne possède aucune entrée Login Web publique capable de transporter le token CSRF et l'`Idempotency-Key` requis par l'endpoint IAM. Le workspace qui expose un token CSRF requiert déjà la Session à créer.

La cause n'est ni HTTPS, ni F1, ni F2, ni PostgreSQL. Elle est exclusivement l'absence de composition Web d'entrée IAM. La résoudre nécessite une autorisation distincte pour IAM Web Entry ; aucun affaiblissement CSRF, cookie ou policy n'est recevable.

F1 et F2 restent inchangées. Aucun Runtime, Controller, Middleware, route, cookie, politique ou migration n'est modifié par F3.
