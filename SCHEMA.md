# Schéma E-A (Merise) - Site d'adoption

## Entités et attributs

**UTILISATEUR** (id, nom, email, mot_de_passe, role, actif)
**CATEGORIE** (id, nom)
**ANIMAL** (id, nom, description, age, sexe, statut)
**FAVORI** (id, commentaire_personnel)
**DEMANDE_ADOPTION** (id, date_demande, statut, commentaire_gestion)

## Associations et cardinalités

- UTILISATEUR (0,n) ----possède----> (1,1) ANIMAL
- CATEGORIE (0,n) ----classe----> (1,1) ANIMAL
- UTILISATEUR (0,n) ----met_en_favori----> (0,n) ANIMAL (porte : commentaire_personnel)
- UTILISATEUR (0,n) ----demande----> (0,n) ANIMAL (porte : date, statut, commentaire_gestion)

## Schéma relationnel

- utilisateurs(id, nom, email, mot_de_passe, role, actif)
- categories(id, nom)
- animaux(id, nom, #categorie_id, description, age, sexe, statut, #proprietaire_id)
- favoris(id, #utilisateur_id, #animal_id, commentaire_personnel)
- demandes_adoption(id, #utilisateur_id, #animal_id, date_demande, statut, commentaire_gestion)
