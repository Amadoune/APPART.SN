# Parent mutation

Le parent d'un Place est readonly dans l'Aggregate actuel : aucun use case de reparenting n'existe. Une mutation directe de relation hors Domain est corruption.

Si un futur reparenting est certifié, il devra augmenter la version du descendant et produira un nouveau vecteur/ordre; cette authority devra être réouverte avant son implémentation.
