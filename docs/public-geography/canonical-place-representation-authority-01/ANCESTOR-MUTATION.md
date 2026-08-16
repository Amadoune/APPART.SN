# Ancestor mutation

Rename/disable/merge d'un ancêtre augmente sa version Aggregate. Le vecteur du descendant change donc sans modifier sa version locale.

Rename produit une nouvelle représentation. Disabled ou merged rend la chaîne non publiable et arrête la nouvelle matérialisation fail-closed; aucune suppression silencieuse de l'ancienne décision.
