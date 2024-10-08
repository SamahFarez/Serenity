-- Supabase AI is experimental and may produce incorrect answers
-- Always verify the output before executing

-- Users table
create table
  Users (
    id_user bigint primary key generated always as identity,
    first_name text not null,
    last_name text not null,
    email text not null unique,
    password text not null,
    cle_carte text not null unique
  );

-- Cartes table
CREATE TABLE Cartes (
    id_carte BIGINT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,  -- Auto-incrementing primary key
    id_user BIGINT NOT NULL,  -- Foreign key reference to the Users table
    card_id TEXT NOT NULL UNIQUE,  -- Unique identifier for the card
    fonctionnel BOOLEAN NOT NULL,  -- Indicates whether the card is functional
    usage_status BOOLEAN NOT NULL DEFAULT false,  -- Indicates if the card has been used (default is false)
    expiry_date TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now() + INTERVAL '1 year',  -- Expiry date (default 1 year from now)
    start_session_time TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),  -- Start time for the session (default is now)
    
    -- Foreign key constraint linking to the Users table
    FOREIGN KEY (id_user) REFERENCES Users (id_user) ON DELETE CASCADE
);

-- Reservations table
create table
  Reservations (
    id_reservation bigint primary key generated always as identity,
    id_user bigint not null,
    date timestamp with time zone not null,
    heure timestamp with time zone not null,
    date_creation timestamp with time zone not null default current_timestamp,
    unique (date, heure),
    foreign key (id_user) references Users (id_user) on delete cascade
  );

-- Personnels table
create table
  Personnels (
    id_personnel bigint primary key generated always as identity,
    nom text not null,
    prenom text not null,
    id_carte bigint not null,
    foreign key (id_carte) references Cartes (id_carte) on delete cascade
  );

-- Salles table
create table
  Salles (
    id_salle bigint primary key generated always as identity,
    nom text not null unique,
    capacite int not null,
    id_personnel bigint not null,
    foreign key (id_personnel) references Personnels (id_personnel) on delete cascade
  );

-- Clients table
create table
  Clients (
    id_client bigint primary key generated always as identity,
    nom text not null,
    prenom text not null,
    email text not null unique,
    id_carte bigint not null,
    foreign key (id_carte) references Cartes (id_carte) on delete cascade
  );

-- Portiques table
create table
  Portiques (
    id_portique bigint primary key generated always as identity,
    nom text not null,
    lieu text not null,
    fonctionnel boolean not null
  );

-- Blacklist table
create table
  Blacklist (
    id_blacklist bigint primary key generated always as identity,
    id_user bigint not null,
    id_client bigint,
    id_carte bigint not null,
    foreign key (id_user) references Users (id_user) on delete cascade,
    foreign key (id_client) references Clients (id_client) on delete cascade,
    foreign key (id_carte) references Cartes (id_carte) on delete cascade
  );

-- Bancs table
create table
  Bancs (
    id_banc bigint primary key generated always as identity,
    nom text not null
  );

-- Etablissements table
create table
  Etablissements (
    id_etablissement bigint primary key generated always as identity,
    nom text not null,
    capacite int not null,
    lieu text not null
  );
