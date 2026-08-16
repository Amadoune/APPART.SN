# Workspace Selection Flow

## Navigation

1. Le workspace charge `country` sans parent.
2. Après sélection d’un item, il interroge uniquement les types enfants permis par le catalogue `PlaceType` pour ce parent.
3. Lorsque plusieurs types sont permis, par exemple `department` et `city` sous une Region, chaque groupe est chargé indépendamment et seuls les groupes non vides sont affichés.
4. Un changement de parent annule les requêtes en cours et efface tous les descendants sélectionnés.
5. La pagination suit exclusivement `nextCursor` dans le même scope type + parent.
6. L’utilisateur choisit explicitement un item retourné. Aucune valeur n’est construite depuis son label.

Le parcours supporte donc les branches réelles suivantes sans exiger tous les niveaux : Region vers Department ou City, Department vers City ou District, City vers District ou Neighborhood, District vers Neighborhood.

## États UX minimaux

- loading par niveau ;
- Empty : niveau absent, sans option fictive ;
- Missing : parent devenu invalide, descendants reset et message de rechargement ;
- indisponibilité : conservation visuelle du choix précédent, sauvegarde bloquée ;
- corruption : sauvegarde bloquée et erreur générique ;
- erreur formulaire non Geography : conservation de l’item et de son contexte de page pour revalidation.

## Sélection finale

Un item réellement retourné peut être choisi lorsque l’utilisateur estime la précision suffisante ou lorsque la branche n’a plus d’enfant. Aucun type terminal artificiel n’est imposé par F4-A ; la Promotion conserve la validation Domain finale.

Le workspace conserve en mémoire : item choisi, `type`, `parentPlaceId`, curseur d’entrée de la page et `limit`. Seul `geographicPlaceId` deviendra un fait persistant F4.
