# 📐 Modèle Conceptuel des Données (Merise)

## Entités et Attributs
* **UTILISATEUR** (id, nom, email, mot_de_passe, role, actif)
* **CATEGORIE** (id, nom)
* **ANIMAL** (id, nom, description, age, sexe, statut)

## Associations et Cardinalités
* **proposer** : UTILISATEUR (0,n) <---> (1,1) ANIMAL
* **classer** : CATEGORIE (0,n) <---> (1,1) ANIMAL
* **favoris** (porte : *commentaire_personnel*) : UTILISATEUR (0,n) <---> (0,n) ANIMAL
* **demande_adoption** (porte : *statut, commentaire_gestion*) : UTILISATEUR (0,n) <---> (0,n) ANIMAL

---

# 📊 Diagramme Entité-Association (MCD)

```mermaid
erDiagram
    UTILISATEUR {
        int id PK
        string nom
        string email
        string mot_de_passe
        string role
        boolean actif
    }
    CATEGORIE {
        int id PK
        string nom
    }
    ANIMAL {
        int id PK
        string nom
        string description
        int age
        char sexe
        string statut
        int categorie_id FK
        int proprietaire_id FK
    }
    FAVORI {
        int id PK
        int utilisateur_id FK
        int animal_id FK
        string commentaire_personnel
    }
    DEMANDE_ADOPTION {
        int id PK
        int utilisateur_id FK
        int animal_id FK
        timestamp date_demande
        string statut
        string commentaire_gestion
    }

    UTILISATEUR ||--o{ ANIMAL : "propose (0,n)"
    CATEGORIE ||--o{ ANIMAL : "classe (0,n)"
    UTILISATEUR ||--o{ FAVORI : "ajoute_favori (0,n)"
    ANIMAL ||--o{ FAVORI : "concerne_favori (1,1)"
    UTILISATEUR ||--o{ DEMANDE_ADOPTION : "fait_demande (0,n)"
    ANIMAL ||--o{ DEMANDE_ADOPTION : "concerne_demande (1,1)"
```

---

# 💾 Modèle Logique des Données (MLD)

* **utilisateurs** (id [PK], nom, email [UNIQUE], mot_de_passe, role, actif)
* **categories** (id [PK], nom)
* **animaux** (id [PK], nom, #categorie_id [FK], description, age, sexe, statut, #proprietaire_id [FK])
* **favoris** (id [PK], #utilisateur_id [FK], #animal_id [FK], commentaire_personnel)
* **demandes_adoption** (id [PK], #utilisateur_id [FK], #animal_id [FK], date_demande, statut, commentaire_gestion)
