# Transition Evidence Requirement Validation 01 — Historical Analysis

La première baseline Git consultable contient déjà `TransitionReason` dans `TransitionEvidence`. Le test historique affirme qu'il est « mandatory », mais vérifie uniquement que la chaîne vide est rejetée.

Les documents Sprint 3.1 énumèrent « motif/acteur/date présents » parmi les invariants et décrivent la révision persistée complète. Ils ne décrivent aucune décision métier, autorité, lecture ou divergence de comportement liée au motif.

Les fixtures utilisent des textes génériques tels que « Motif métier obligatoire », « Projection fixture transition » ou « Runtime certification ». Leur interchangeabilité sans effet observable montre qu'elles satisfont surtout la forme du modèle.

Conclusion historique : obligation structurelle présente dès la baseline, justification métier spécifique non retrouvée. La qualification `HISTORICAL_ONLY` s'applique à la justification de l'obligation, pas nécessairement au champ de trace lui-même.
