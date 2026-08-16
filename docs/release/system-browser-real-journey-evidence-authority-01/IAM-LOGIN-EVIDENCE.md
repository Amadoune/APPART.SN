# Preuve de Login IAM

La preuve minimale est :

1. ouverture du formulaire réel `/connexion` ;
2. saisie opérateur d'un credential explicitement autorisé ;
3. POST Login réel avec CSRF et idempotency identity produits par l'application ;
4. réponse applicative réelle ;
5. émission de `Set-Cookie: __Host-appart_session` ;
6. métadonnées `Secure`, `HttpOnly`, `SameSite=Strict` observées sans consigner la valeur ;
7. navigation vers la destination exacte ;
8. accès au workspace ou à Publication Review selon l'actor ;
9. corroboration de la session par l'autorité IAM existante si nécessaire.

L'AccountId provient exclusivement de la session. Aucun cookie, SessionId ou AccountId n'est copié ou injecté.
