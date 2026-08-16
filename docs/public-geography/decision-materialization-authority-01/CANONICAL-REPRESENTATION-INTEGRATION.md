# Canonical representation integration

Le materializer relira le terminal Place, suivra chaque parent jusqu'à Country, détectera missing/cycle/type/pays/availability, inversera la chaîne et produira root→leaf.

Chaque nœud fournit PlaceId, PlaceType, officialName, parentPlaceId et aggregateVersion. Le payload reprend locality terminale, breadcrumb typé et revision vector. Aucun label legacy, état UI, tri SQL, Projection ou URL.
