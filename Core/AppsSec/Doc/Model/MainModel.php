<?php

namespace Core\AppsSec\Doc\Model;

use Core\Lib\Amvc\Model;

/**
 *
 * @author Michael "Tekkla" Zorn
 *
 */
final class MainModel extends Model
{

	public function createMenu()
	{
		$groups = $this->getModel('Group')->getGroups();

		$menu = [];

		foreach ($groups as $group)
		{
			$menu_item = array(
				'node' => $groupdom,
				'title' => $groupheadline,
			);

			if (count($menu) == 0)
				$menu_item['active'] = true;

			if ($groupdocs)
			{
				$menu_itemsubs = [];

				foreach ($groupdocs as $doc)
				{
					$menu_item['subs'][] = array(
						'node' => $docdom,
						'title' => $doctitle,
					);
				}
			}

			$menu[] = $menu_item;
		}

		return $menu;
	}
}

