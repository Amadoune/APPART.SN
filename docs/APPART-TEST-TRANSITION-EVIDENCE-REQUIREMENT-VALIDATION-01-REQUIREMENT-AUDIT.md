# Transition Evidence Requirement Validation 01 — Requirement Audit

## Conclusion

Pour Submit, BeginReview et ApproveAndPublish, `TransitionReason` n'est pas un invariant de décision métier démontré. Il est obligatoire parce que `TransitionEvidence`, `ListingRevision`, le mapper et le schéma persistant l'imposent.

Le Domain décide à partir de l'état, du trigger, de l'origin, de l'éligibilité Property, et pour Publish des médias et de l'expiration. Le contenu du reason n'est jamais inspecté au-delà de sa longueur.

Le reason est conservé comme trace descriptive : révision persistée puis événement. Aucun lecteur fonctionnel, policy, projection ou comportement aval identifié ne le relit.
