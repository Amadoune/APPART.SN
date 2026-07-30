# Sprint 3.9C — Multi-target Consumer Integration

Le Consumer conserve le chemin mono-cible 3.6C.6 et accepte désormais une résolution
`MultiTargetResolved`. Cette résolution contient la requête normalisée certifiée par ADR-1010 ; le
Consumer ne déduit aucune Property et ne reconstruit aucune source.

Pendant une tentative, il demande les pages dans l'ordre, exécute chaque Listing séquentiellement et
s'arrête au premier résultat non consommable. Le message source ne reçoit un résultat terminal
positif qu'après `NoTargets` ou la dernière page `Completed`.

Il n'existe aucune persistance de checkpoint. Après crash, l'Outbox redélivre le même message ; la
résolution recommence dans le même ordre et les effets antérieurs convergent vers `AlreadyApplied`.
