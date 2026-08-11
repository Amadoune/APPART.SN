# IAM Web Entry Experience 01 — Certification Note

## Verdict

**NO GO PROPOSÉ**

La composition applicative existe derrière la frontière IAM, mais le transport local ne permet pas d'établir la session navigateur certifiée. `http://appart.test` est incompatible avec le cookie `__Host-appart_session; Secure`, tandis que `https://appart.test` n'est pas servi en TLS.

Le NO GO ne signale aucun défaut du domaine IAM ni des capacités Authoring/Media. Il identifie un prérequis d'environnement local manquant. Aucun contournement de sécurité et aucune expérience simulée n'ont été introduits.

## Condition de réouverture

Qualifier et activer HTTPS local pour `appart.test`, puis rejouer ce sprint depuis l'accueil jusqu'au workspace avec une authentification réelle et un compte local autorisé.

**NO GO PROPOSÉ — APPART.SN PRODUCT SPRINT — IAM WEB ENTRY EXPERIENCE 01**
