# Media Item Lifecycle Migration and Rollback Guide

La migration additive **031** crée uniquement le journal et son index dans le schéma `media`. Elle ne modifie pas les tables historiques de collection.

Le rollback 031 supprime exclusivement `media.media_item_lifecycle_transitions`. Il ne supprime ni le schéma, ni `media_collections`, ni `media_items`, ni les réservations existantes.
