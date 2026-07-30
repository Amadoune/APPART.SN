# Phase 5.1I — HTTP Security Certification

Les tests ciblés couvrent :

1. cookie sécurisé, HttpOnly et Strict sans secret dans le body ;
2. homogénéité des erreurs login ;
3. homogénéité recovery compte existant/absent ;
4. refus des endpoints protégés sans session ;
5. auto-scope du compte issu de la session ;
6. refus des champs inconnus et idempotency manquante ;
7. rate limiting effectif sans écho de l'identifiant ;
8. absence de dépendance HTTP vers 5.1F, 5.1G ou 5.1H.

Résultat ciblé : **9 tests, 50 assertions, PASS**.
