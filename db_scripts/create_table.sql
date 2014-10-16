CREATE TABLE `mitglieder` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anrede` enum('herr','frau','firma') NOT NULL DEFAULT 'herr',
  `vorname` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `strasse` varchar(50) NOT NULL,
  `plz` varchar(5) NOT NULL,
  `ort` varchar(50) NOT NULL,
  `land` char(2) NOT NULL DEFAULT 'de',
  `email` varchar(50) NOT NULL,
  `passwort_hash` varchar(255) NOT NULL,
  `verifizierung_hash` varchar(40) DEFAULT NULL,
  `status` enum('angemeldet','aktiv','inaktiv') NOT NULL DEFAULT 'inaktiv',
  `datum_erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datum_geaendert` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=0  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;
