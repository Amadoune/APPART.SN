# P04 — Property Page Premium

## Objet

Le sprint améliore exclusivement la fiche annonce publique existante. La route canonique et `PublicListingQuery` restent les seules frontières de lecture ; aucune donnée Authoring, Aggregate, Draft, Registry interne ou SQL direct n'est introduite.

## Expérience livrée

- en-tête et navigation du Product Experience Shell conservés ;
- fil d'Ariane, badges transaction/type et titre public hiérarchisés ;
- prix absent représenté honnêtement par « Prix non communiqué » ;
- média principal issu de la projection publique, avec texte alternatif contextuel ;
- caractéristiques publiques non vides uniquement : transaction, type, surface, pièces et ville ;
- description et localisation rendues uniquement lorsqu'elles sont publiques ;
- CTA de contact visible mais désactivé avec « Bientôt disponible » ;
- adaptation desktop, tablette et mobile sans débordement horizontal.

## Limites explicites

Le read model certifié expose un seul `publicMediaUrl` : la galerie P04 est donc mono-média et n'invente aucune vignette. Il n'existe pas de surface certifiée pour les biens similaires : cette section est volontairement absente. Aucun prix, contact, carte, favori ou contenu métier n'est déduit.

## Parcours

`Accueil → Recherche → Résultats réels → /annonces/p03-appartement-a-vendre-dakar`
