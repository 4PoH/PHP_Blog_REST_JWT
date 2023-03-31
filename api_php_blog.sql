-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 31 mars 2023 à 21:11
-- Version du serveur : 5.7.36
-- Version de PHP : 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `api_php_blog`
--

-- --------------------------------------------------------

--
-- Structure de la table `appartient`
--

DROP TABLE IF EXISTS `appartient`;
CREATE TABLE IF NOT EXISTS `appartient` (
  `idArticle` int(11) NOT NULL,
  `idCategorie` int(11) NOT NULL,
  PRIMARY KEY (`idArticle`,`idCategorie`),
  KEY `idCategorie` (`idCategorie`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `appartient`
--

INSERT INTO `appartient` (`idArticle`, `idCategorie`) VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 3);

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
  `idArticle` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(25) NOT NULL,
  `datePubli` datetime DEFAULT CURRENT_TIMESTAMP,
  `dateModif` datetime DEFAULT NULL,
  `contenu` varchar(500) NOT NULL,
  PRIMARY KEY (`idArticle`),
  KEY `nom` (`nom`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`idArticle`, `nom`, `datePubli`, `dateModif`, `contenu`) VALUES
(1, 'CharliePublisher', '2023-03-31 22:27:18', '2023-03-31 22:30:13', 'Ceci est le contenu de l article 1'),
(2, 'BobPublisher', '2023-03-31 22:29:35', NULL, 'Ceci est le contenu de l article 2'),
(3, 'BobPublisher', '2023-03-31 22:29:35', '2023-03-31 22:30:22', 'Ceci est le contenu de l article 3');

--
-- Déclencheurs `article`
--
DROP TRIGGER IF EXISTS `Article_IniDateAjout`;
DELIMITER $$
CREATE TRIGGER `Article_IniDateAjout` BEFORE INSERT ON `article` FOR EACH ROW SET NEW.datePubli = NOW() AND NEW.dateModif = NOW()
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `Article_dateMiseAJour`;
DELIMITER $$
CREATE TRIGGER `Article_dateMiseAJour` BEFORE UPDATE ON `article` FOR EACH ROW SET NEW.dateModif = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `idCategorie` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(30) NOT NULL,
  PRIMARY KEY (`idCategorie`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`idCategorie`, `libelle`) VALUES
(1, 'Technologie'),
(2, 'Science'),
(3, 'Divertissement');

-- --------------------------------------------------------

--
-- Structure de la table `evalue`
--

DROP TABLE IF EXISTS `evalue`;
CREATE TABLE IF NOT EXISTS `evalue` (
  `idArticle` int(11) NOT NULL,
  `nom` varchar(25) NOT NULL,
  `pouceBleu` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`idArticle`,`nom`),
  KEY `nom` (`nom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `evalue`
--

INSERT INTO `evalue` (`idArticle`, `nom`, `pouceBleu`) VALUES
(1, 'BobPublisher', 1),
(1, 'CharliePublisher', 0),
(2, 'BobPublisher', 1),
(2, 'CharliePublisher', 1),
(3, 'CharliePublisher', 0),
(3, 'BobPublisher', 0);

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `idRole` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(20) NOT NULL,
  PRIMARY KEY (`idRole`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`idRole`, `libelle`) VALUES
(1, 'moderator'),
(2, 'publisher');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `nom` varchar(25) NOT NULL,
  `mdp` varchar(30) NOT NULL,
  `idRole` int(11) NOT NULL,
  PRIMARY KEY (`nom`),
  KEY `idRole` (`idRole`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`nom`, `mdp`, `idRole`) VALUES
('AliceModerator', 'password1', 1),
('BobPublisher', 'password2', 2),
('CharliePublisher', 'password3', 2);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
