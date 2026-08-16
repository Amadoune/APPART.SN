# Media Rehydration Evidence

La reprise appelle la lecture Media owner-scoped avec l'AccountId de session et le PropertyId recoupé.

Le snapshot expose uniquement collection, version et metadata des items. Aucune URL publique ni binaire privé n'est ajouté. Le frontend affiche les captions et le nombre de médias ; l'absence d'object URL n'empêche pas la reprise fonctionnelle.

`Unavailable` devient `DependencyUnavailable`, un refus owner devient `NotFoundOrForbidden`, et tout statut inattendu est refusé comme corruption.
