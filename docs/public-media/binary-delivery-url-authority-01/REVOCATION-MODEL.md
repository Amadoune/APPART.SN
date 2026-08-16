# Revocation model

La révocation est dynamique et fail-closed. Le GET cesse de servir dès que l'item est retiré/archivé, l'attachment n'est plus valide, l'asset n'est plus ready/intègre, le Listing n'est plus Published ou la relation owner est incohérente.

Un remplacement crée une nouvelle identité/révision publique. L'ancien locator répond 404. Aucun redirect automatique.
