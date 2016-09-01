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
		return [
				'name' => $this->getName(),
				'affiliation' => $this->getAffiliation(),
				'agenda' => $slots->getAgenda(),
				'draw_deck_size' => $slots->getDrawDeck()->countCards(),
				'plot_deck_size' => $slots->getPlotDeck()->countCards(),
				'included_packs' => $slots->getIncludedPacks(),
				'slots_by_type' => $slots->getSlotsByType()
		];
	}
}