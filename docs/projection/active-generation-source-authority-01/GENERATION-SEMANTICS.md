# Generation Semantics

Une génération est une version complète et isolée du read model Public Projection. Elle sert d'epoch de reconstruction: les records candidats sont écrits dans son espace, validés ensemble, puis rendus visibles par une bascule atomique. Elle n'est ni une version de Listing, ni une release applicative, ni un tenant.
