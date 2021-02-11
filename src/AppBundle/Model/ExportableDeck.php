<?php

namespace AppBundle\Model;

class ExportableDeck
{
	public function getArrayExport($withUnsavedChanges = false)
	{
		$slots = $this->getSlots();
		$array = [
				'id' => $this->getId(),
				'name' => $this->getName(),
				'date_creation' => $this->getDateCreation()->format('c'),
				'date_update' => $this->getDateUpdate()->format('c'),
				'description_md' => $this->getDescriptionMd(),
				'format_code' => $this->getFormat()->getCode(),
				'format_name' => $this->getFormat()->getName(),
				'user_id' => $this->getUser()->getId(),
				'affiliation_code' => $this->getAffiliation()->getCode(),
				'affiliation_name' => $this->getAffiliation()->getName(),
				'slots' => $slots->getContent(),
				'characters' => $slots->getCharacterDeck()->getContent(),
				'version' => $this->getVersion(),
		];

		return $array;
	}

	public function getTextExport()
	{
		$slots = $this->getSlots();

		$decklist_factions = $slots->getCountByFaction();
        arsort($decklist_factions);
        $factions = array_keys(array_filter($decklist_factions, function($v) {
            return $v > 0;
        }));

		return [
				'name' => $this->getName(),
				'affiliation' => $this->getAffiliation(),
				'factions' => $factions,
				'included_sets' => $slots->getIncludedSets(),
				'slots_by_type' => $slots->getSlotsByType()
		];
	}

	public function getTtsExport()
	{
		$slots = $this->getSlots();

		$guidArray = [];
		for ($i = 1; $i <= 50; $i++) {
			array_push($guidArray, bin2hex(openssl_random_pseudo_bytes(3)));
		}

		$decklist_factions = $slots->getCountByFaction();
        arsort($decklist_factions);
        $factions = array_keys(array_filter($decklist_factions, function($v) {
            return $v > 0;
        }));

		return [
				'name' => $this->getName(),
				'affiliation' => $this->getAffiliation(),
				'factions' => $factions,
				'included_sets' => $slots->getIncludedSets(),
				'slots_by_type' => $slots->getSlotsByType(),
				'guidArray' => $guidArray
		];
	}
}
