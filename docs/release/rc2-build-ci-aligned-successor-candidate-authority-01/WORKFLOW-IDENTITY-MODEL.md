# Workflow Identity Model

Le workflow actuel vérifie réellement :

- un événement ou dispatch associé au tag candidat exact ;
- le type annoté du tag ;
- la résolution exacte `tag → GITHUB_SHA` ;
- l'ascendance de `SOURCE_BASE_SHA` vers `GITHUB_SHA` ;
- la propreté du checkout.

Il n'exige aucun tree préconnu. Le futur alignement doit remplacer uniquement les identités actives par la base RC2 et le tag successor exact, sans wildcard, fallback ou relâchement des vérifications.
