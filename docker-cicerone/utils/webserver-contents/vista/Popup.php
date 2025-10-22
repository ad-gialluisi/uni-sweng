<?php
// Copyright (C) 2020 Antonio Daniele Gialluisi

// This file is part of "Piattaforma Cicerone"

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program. If not, see <https://www.gnu.org/licenses/>.


namespace vista;

require_once "TriplettaTemplate.php";


/**
 * Rappresenta una schermata "popup", inteso come una piccola
 * finestrella con dei contenuti.
 */
class Popup extends TriplettaTemplate {
    /**
     * Lista delle triplette associate
     * @var array
     */
    private $tripletteAssociate;


    /**
     * Tipo di chiusura utilizzata.
     * @var string
     */
    private $tipoChiusura;


    /**
     * La chiusura "modal" implica che per eliminare
     * il popup, bisognerà cliccare al di fuori di esso.
     */
    public const CHIUSURA_MODAL = "modal";


    /**
     * La chiusura "interactive" implica che per eliminare
     * il popup, bisognerà cliccare sull'icona di chiusura.
     */
    public const CHIUSURA_INTERACTIVE = "interactive";


    /**
     * Crea un popup dandogli un certo nome (tale nome, verrà usato come id
     * nel DOM HTML) ed un tipo di chiusura.
     * @param string $nomePopup
     * @param string $tipoChiusura il tipo di chiusura da utilizzare.
     */
    public function __construct(string $nomePopup, string $tipoChiusura=self::CHIUSURA_MODAL) {
        parent::__construct("popup", "popup");

        $this->html->add("popup-nome", $nomePopup);
        $this->html->add("tipo-chiusura", $tipoChiusura);
        $this->html->setPulsante("is-chiusura-interattiva",
            $tipoChiusura === self::CHIUSURA_INTERACTIVE);
        $this->tripletteAssociate = array();
        $this->tipoChiusura = $tipoChiusura;
    }


    /**
     * Aggiunge un elemento come contenuto del popup.
     * @param string $contenuti
     */
    public function add($elemento) : void {
        if ($elemento instanceof TriplettaTemplate) {
            $firma = $elemento->getFirmaTripletta();
            if (!isset($this->tripletteAssociate[$firma])) {
                $this->tripletteAssociate[$firma] = $elemento;
            }
        }

        $this->html->add("popup-contenuto", $elemento);
    }


    /**
     * Restituisce la lista di triplette Template aggiunte
     * tra i contenuti del Popup.
     * @return array
     */
    public function getTripletteAssociate() : array {
        return $this->tripletteAssociate;
    }
}




