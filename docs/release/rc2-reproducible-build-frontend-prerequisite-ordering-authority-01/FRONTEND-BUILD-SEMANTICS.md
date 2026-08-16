# Frontend Build Semantics

Les Feature tests de Release doivent rendre les vues dans les conditions de l'application packagée. Le build Vite réel est donc un prérequis de test, pas un mock.

Le build produit avant les suites peut être réutilisé par Packaging A : le script certifié copie `public/build` dans la racine Release. Packaging ne reconstruit historiquement pas le frontend ; le workflow le construit une fois avant l'appel du script.

La source du build reste le lockfile et les assets de la même candidate.
