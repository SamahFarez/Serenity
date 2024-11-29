# SQL Queries for Spa Reservation System

## 1. Insert a New Membre (Client)


INSERT INTO Membre (id_membre, nom, prenom, email, num_tel, adresse)
VALUES (1202, 'Doe', 'Leo', 'leo.doe@gmail.com', '0792375930', '10 avenue de la rue, Paris');


## 2. Insert a New Client (User)


INSERT INTO Client (id_client, password)
VALUES (12002, 'Lololol ?123');


## 3. Insert a New Personnel Member (Employee)


INSERT INTO Personnel (id_personnel, salaire, poste, nom, prenom, num_tel, email)
VALUES (123, 3000, 'Thérapeute', 'Martin', 'Claire', '0789456789', 'claire@spa.fr');


## 4. Insert a New Reservation


INSERT INTO Reservation (id_reservation, id_client, id_service, date, heure, statut, commentaires, date_creation)
VALUES (1223, 12002, 301, '2024-10-20', '20:00', 'En attente', 'Aucune préférence', NOW());


## 5. Insert a New Service

INSERT INTO Service (id_service, nom, description, prix, duree)
VALUES (301, 'Massage Suédois', 'Massage relaxant et revitalisant', 75.00, '01:30:00');


## 6. Insert a New Salle (Room)


INSERT INTO Salle (id_salle, nom, capacite)
VALUES (501, 'Salle 1', 3);


## 7. Insert a New Portique (Portico)


INSERT INTO Portique (id_portique, nom, lieu, fonctionnel)
VALUES (1223, 'Portique A', 'Entrée principale', TRUE);


## 8. Link Membre to Client


INSERT INTO Est_Dans (id_membre, id_client)
VALUES (1202, 12002);


## 9. Blacklisting a Client


INSERT INTO Blacklist (id_blacklist, id_client, raison, date_creation)
VALUES (1223, 12002, 'Comportement inapproprié', NOW());


## 10. Insert a New Banc (Bench)


INSERT INTO Banc (id_banc, nom, id_salle)
VALUES (1223, 'Banc salle 1', 501);


## 11. Insert a New Etablissement (Establishment)


INSERT INTO Etablissement (id_etablissement, nom, capacite, lieu, type)
VALUES ('SERENITY1', 'Serenity - Paris', 20, 'Paris', 'Bien-être');


## 12. Install a Bench into an Establishment


INSERT INTO Installer (id_etablissement, id_banc)
VALUES ('SERENITY1', 1223);


## 13. Check all Reservations of a Client


SELECT * FROM Reservation
WHERE id_client = 12002;


## 14. Find Available Rooms for a Given Date and Time


SELECT * FROM Salle
WHERE id_salle NOT IN (
    SELECT id_salle FROM Reservation
    WHERE date = '2024-10-20' AND heure = '20:00'
);


## 15. Update Reservation Status

UPDATE Reservation
SET statut = 'Confirmée'
WHERE id_reservation = 1223;


## 16. Delete a Blacklisted Client


DELETE FROM Blacklist
WHERE id_client = 12002;

## 17. Update a Client's Phone Number


UPDATE Membre
SET num_tel = '0743123456'
WHERE id_membre = 1202;


## 18. Check All Reservations for a Given Service


SELECT r.id_reservation, r.date, r.heure
FROM Reservation r
JOIN Service s ON r.id_service = s.id_service
WHERE s.nom = 'Massage Suédois';


## 19. Find Available Portiques (Porticos)

SELECT * FROM Portique
WHERE fonctionnel = TRUE;


## 20. Find All Services with a Price Greater than a Specific Amount


SELECT * FROM Service
WHERE prix > 50;


## 21. Add a New Card for a Client


INSERT INTO Carte (id_carte, id_membre)
VALUES (1234, 1202);


## 22. List All Personnel Members by Their Role (Poste)


SELECT nom, prenom, poste
FROM Personnel
WHERE poste = 'Thérapeute';


## 23. Find All Clients Who Have Made Reservations


SELECT DISTINCT c.nom, c.prenom, r.date, r.heure
FROM Client c
JOIN Reservation r ON c.id_client = r.id_client
ORDER BY r.date, r.heure;


## 24. Check Blacklist Status for a Client


SELECT * FROM Blacklist
WHERE id_client = 12002;

## 25. Check if a Room is Occupied for a Given Date and Time


SELECT id_salle, nom FROM Salle
WHERE id_salle NOT IN (
    SELECT id_salle FROM Reservation
    WHERE date = '2024-10-20' AND heure = '20:00'
);