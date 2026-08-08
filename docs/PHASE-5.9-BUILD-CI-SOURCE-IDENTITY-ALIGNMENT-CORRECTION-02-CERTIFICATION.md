# Certification

Statut : `GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

Objectif exclusif : terminer la qualification de la convention Build/CI déjà auditée, maintenant que PostgreSQL global est vert.

Convention : R2 est la source de base ancestrale immuable ; un futur tag annoté R3 devra résoudre exactement vers le commit candidat construit. Aucun SHA auto-référent n'est inscrit dans le contenu.

Toutes les campagnes globales sont terminales et PASS. Les trois contrôles partagent la même convention fermée, sans wildcard, fallback ni auto-référence. Les fichiers protégés sont inchangés.

La candidate est matériellement admissible : un commit descendant de R2 peut être identifié après sa création par le tag annoté exact `phase-5.9-baseline-candidate-r3`, puis refusé si tag, commit ou ascendance divergent. R3, son tag et Evidence 04 ne sont toutefois pas créés pendant ce jalon, conformément à l'interdiction explicite.

Verdict : `GO PROPOSÉ — PHASE-5.9-BUILD-CI-SOURCE-IDENTITY-ALIGNMENT-CORRECTION-02`.
