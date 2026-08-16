# Final Version Model

- absence de décision courante : candidate version `1` ;
- mêmes révisions, même policy et même projection canonique : version courante, `AlreadyApplied` ;
- candidate dominant la décision courante par ses révisions owners : `current.version + 1` ;
- candidate dominée : `RejectedObsolete` ;
- versions de sources identiques avec factId/effectiveAt différents, ou ensembles incomparables : `Divergent` ;
- changement de policy : `current.version + 1` après recalcul complet.

« Dominant » signifie : chaque version source candidate est supérieure ou égale à la courante, au moins une est supérieure, et toute version égale désigne exactement le même fait.

Le Reader fournit l'état courant ; le Writer reste l'arbitre transactionnel final. Aucune séquence globale ni migration n'est requise.
