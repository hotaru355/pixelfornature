CREATE TABLE `mitglieder` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `facebook_id` varchar(20) DEFAULT NULL,
  `anrede` enum('herr','frau','firma') NOT NULL DEFAULT 'herr',
  `vorname` varchar(50) NOT NULL,
  `nachname` varchar(50) NOT NULL,
  `strasse` varchar(50) DEFAULT NULL,
  `plz` varchar(5) DEFAULT NULL,
  `ort` varchar(50) DEFAULT NULL,
  `land` char(2) NOT NULL DEFAULT 'de',
  `email` varchar(50) NOT NULL,
  `telefon` varchar(50) DEFAULT NULL,
  `passwort_hash` varchar(255) NOT NULL,
  `verifizierung_hash` varchar(40) DEFAULT NULL,
  `status` enum('angemeldet','aktiv','inaktiv') NOT NULL DEFAULT 'inaktiv',
  `datum_erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datum_geaendert` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=0  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

CREATE TABLE `projekte` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `timeline_name` varchar(255) DEFAULT NULL,
  `beschreibung` text DEFAULT NULL,
  `bild_url` varchar(255) DEFAULT '/images/projekt.jpg',
  `seite_url` varchar(255) DEFAULT NULL,
  `status` enum('aktiv','inaktiv') NOT NULL DEFAULT 'inaktiv',
  `datum_erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datum_geaendert` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

CREATE TABLE `interaktionen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mitglied_id` int(10) unsigned NOT NULL,
  `projekt_id` int(10) unsigned NOT NULL,
  `type` enum('signup','pixelspende') NOT NULL DEFAULT 'pixelspende',
  `pixel_gespendet` bigint(10) DEFAULT 0,
  `datum_erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datum_geaendert` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (mitglied_id) REFERENCES mitglieder(id) ON DELETE CASCADE,
  FOREIGN KEY (projekt_id) REFERENCES projekte(id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;
