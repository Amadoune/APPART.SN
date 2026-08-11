# P08 — Moderation & Publication — Final Implementation

## Expérience livrée

La surface `/publication-review` compose exclusivement les contrats certifiés :

`Session IAM` → `PublicationReviewAuthorizationReaderV1` → `Queue` → `Claim` → `BeginPublicationReviewV1` → `ApprovePublicationV1` → `ProjectPublishedListingV1`.

Les écrans couvrent la file, la fiche, le claim, Begin Review, Approve et la confirmation. Ils sont séparés de Report Moderation et utilisent le Product Design Language existant.

## Autorisation

Chaque opération sélectionne côté serveur une capacité fermée distincte. L'AccountId vient uniquement de `RequireIdentityAccessSession` et devient mécaniquement l'actor. Aucun AccountId, actor ou capability client n'est accepté.

## Publication publique

Approve ne déclenche Projection qu'après un résultat terminal accepté. P08 n'accède ni au Search Runtime ni au Projection Runtime concret et n'effectue aucune écriture Search. La confirmation renvoie vers la recherche publique certifiée.

## Responsive et accessibilité

Les écrans disposent d'un H1 unique, de landmarks, de formulaires CSRF, de boutons clavier natifs, de focus hérité du Design Language, d'états ARIA et de breakpoints tablette/mobile sans grille fixe.
