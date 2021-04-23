<?php

class Travel {
	private static $URL = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';

	public static function getTravels() {
		$travelsArr = getArray(Travel::$URL);

		$map = [];
		foreach ($travelsArr as $travel) {
			$map[$travel->id] = [
				'companyId'=>$travel->companyId,
				'price'=>$travel->price
			];
		}

		return $map;
	}
}

class Company {
	private static $URL = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';

	public static function getCompanies() {
		$companiesArr = getArray(Company::$URL);

		$map = [];
		foreach ($companiesArr as $company) {
			$map[$company->id] = [
				'id'=>$company->id,
				'name'=>$company->name,
				'parentId'=>$company->parentId
			];
		}

		return $map;
	}
}

function getArray($url) {
  $json = file_get_contents($url);
  return json_decode($json);
}

function makeCompanyTree(&$arr, $parentId = 0) {
	$out = [];
	foreach ($arr as $index => $item) {
		if ($item['parentId'] == $parentId) {
			$r = [
				'id' => $item['id'],
				'name' => $item['name'],
				'cost' => $item['cost']
			];
			unset($arr[$index]);
			$children = makeCompanyTree($arr, $item['id']);
			if (count($children) > 0) {
				$totalCost = $r['cost'];
				foreach ($children as $child) {
					$totalCost+=$child['cost'];
				}

				$r['children'] = $children;
				$r['cost'] = $totalCost;
			}
			$out[] = $r;
		}
	}
	return $out;
}

function getCostsForCompanies($arr) {
	$res = [];
	foreach ($arr as $key => $value) {
		if (isset($res[$value['companyId']])) {
			$res[$value['companyId']]['cost'] += $value['price'];
		} else {
			$res[$value['companyId']] = [
				'id' => $value['companyId'],
				'cost' => $value['price']
			];
		}

	}

	return $res;
}

class TestScript {
	public function execute() {
		$start = microtime(true);
		$travels = Travel::getTravels();
		$companyMap = Company::getCompanies();

		$prices = getCostsForCompanies($travels);

		foreach ($prices as $price) {
			$company = &$companyMap[$price['id']];
			$company['cost'] = $price['cost'];
		}

		$tree = makeCompanyTree($companyMap)[0];

		print_r($tree);

		echo 'Total time: '. (microtime(true) - $start);
	}
}

(new TestScript())->execute();

?>