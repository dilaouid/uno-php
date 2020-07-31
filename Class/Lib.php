<?php

namespace Uno;

use PDO;

class Lib {

	private $db;

	public function __construct($db) {
		$this->db = $db;
    }

    /**
    * Sélectionne des valeurs dans la table choisies
    *
    * @param str   $table Nom de la table où effectuer la recherche
    * @param str   $col Nom de la / des colonne(s) à rechercher
    * @param array   $cdt Conditions de la recherche
    * @param array   $value Tableau des valeurs à rechercher (protections injections SQL)
    *
    * @return array
    */
    public function selectSQL($table, $col, $cdt = array(), $value = array()) {
        if (count($cdt) != count($value)) {
            print("Une erreur dans Lib->selectSQL() est survenue. Le nombre de colonnes à mettre à jour est différent du nombre de valeurs.<br>Trace:");
            var_dump($cdt);
            var_dump($value);
            exit();
        }
        $i = 0;
        $prepare = null;
        while ($i < count($cdt)) {
            $and = ' ';
            if (isset($cdt[$i + 1]))
                $and = 'AND';
            $prepare .= $cdt[$i].' = ?'.$and;
            $i++;
        }
        $statement = "SELECT $col FROM $table WHERE $prepare";
        $query = $this->db->prepare($statement);
        $query->execute($value);
        return ($query->fetch(PDO::FETCH_ASSOC));
    }

    /**
    * Sélectionne et fetch des données en table
    *
    * @param str   $table Nom de la table
    * @param str   $col Colonnes à fetch
    * @param str   $cdt Condition de suppression
    * @param array   $value Valeurs à fetch (protection injection SQL)
    * @param bool   $all|false Indique si toutes les données doivent être fetch, ou seulement la première
    *
    * @return null
    */
    public function selectAndFetch($table, $col, $cdt, $value = array(), $all = false) {
        $statement = "SELECT $col FROM $table WHERE $cdt";
        $query = $this->db->prepare($statement);
        $query->execute(array_values($value));
        if ($all === true)
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
        else
            $result = $query->fetch(PDO::FETCH_ASSOC);
        return ($result);
    }

    /**
    * Sélectionne et fetch des données en table
    *
    * @param str   $table Nom de la table
    * @param str   $col Colonnes à fetch
    * @param str   $cdt Condition de suppression
    * @param array   $value Valeurs à fetch (protection injection SQL)
    * @param bool   $all|false Indique si toutes les données doivent être fetch, ou seulement la première
    *
    * @return null
    */
    public function updateCol($table, $col = array(), $cdt, $value = array(), $protected = true) {
        $i = 0;
        if (count($col) != count($value)) {
            print("Une erreur dans Libft->updateCol est survenue. Le nombre de colonnes à mettre à jour est différent du nombre de valeurs.<br>Trace:");
            var_dump($col);
            var_dump($value);
            exit();
        }
        $prepare = null;
        while ($i < count($col)) {
            $coma = ' ';
            if (isset($col[$i + 1]))
                $coma = ', ';
            if ($protected == true) {
                $prepare .= $col[$i].' = ?'.$coma;
            } else {
                $prepare .= $col[$i].' = \''. $value[$i]. '\'' .$coma;
            }
            $i++;
        }
        $statement = "UPDATE $table SET $prepare WHERE $cdt";
        if ($protected == true) {
            $query = $this->db->prepare($statement);
            $query->execute($value);
        } else {
            $query = $this->db->query($statement);
        }
        return true;
    }

    /**
    * Insére des valeurs dans une table
    *
    * @param str   $table Nom de la table
    * @param str   $col Les colonnes où insérer des valeurs
    * @param array   $value Valeurs à insérer (protection injection SQL)
    *
    */
    public function insertSQL($table, $col, $value) {
        $tab = array();
        foreach ($value as $key => $val) {
            if (!is_numeric($val))
               $value[$key] = htmlentities($val);
            $tab[$key] = '?';
        }
        $prepare = implode(', ', $tab);
        $statement = "INSERT INTO $table ($col) VALUES ($prepare)";
        $query = $this->db->prepare($statement);
        $query->execute(array_values($value));
    }

    /**
    * Compte le nombre d'occurence dans une table
    * @param str    $table Nom de la table
    * @param str    $col Nom de la/les colonnes à vérifier
    * @param str    $cdt Condition à vérifier
    * @return number
    */
	public function countOcc($table, $col, $cdt) {
        $statement = "SELECT $col FROM $table WHERE $cdt";
		$nbOcc = $this->db->query($statement);
		return ($nbOcc->rowCount());
	}

    /**
    * Compte le nombre d'occurence dans une table
    * @param str    $msg Le message à afficher
    * @param str    $col Le type d'alerte Bootstrap
    */
    public function createAlert($msg, $type) {
        return "<div class='alert alert-$type' role='alert'>
                <span><strong>$msg</strong></span>
                </div>";
    }

    public function decode($arr, $key) {
        return json_decode($arr, false)->$key;
    }

}