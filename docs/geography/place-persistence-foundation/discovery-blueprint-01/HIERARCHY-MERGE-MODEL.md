# Hierarchy and Merge Model

`parent_place_id` suffit pour représenter la hiérarchie. Le type du parent est relu depuis sa ligne et reconstruit en `AdministrativeDivision`; aucune colonne `parent_type`, closure table ou path matérialisé n'est nécessaire.

Le Domain conserve `PlaceType::acceptsParent`, même pays, parent enabled/non merged et absence d'auto-parentage. Le Repository stocke et garantit seulement les références existantes et non réflexives.

`merged_into_place_id` est null hors fusion, sinon FK vers la cible. La source fusionnée est disabled. La cible absente rend le snapshot impossible/corrompu ; aucune redirection n'est faite par le Repository.

Une Place fusionnée demeure persistée et reconstructible avec son ID original.
