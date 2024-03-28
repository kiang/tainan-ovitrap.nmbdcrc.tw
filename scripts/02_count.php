<?php
$basePath = dirname(__DIR__);
$cunliJson = json_decode(file_get_contents($basePath . '/raw/cunli.json'), true);
$cunliPool = array(
  '永康區塩行里' => '67000310010',
  '安南區公塭里' => '67000350024',
  '安南區塩田里' => '67000350019',
  '安南區塭南里' => '67000350003',
  '永康區塩洲里' => '67000310029',
  '永康區塩興里' => '67000310043',
  '永康區鹽行里' => '67000310010',
);
foreach ($cunliJson['features'] as $f) {
  $cunliPool[$f['properties']['TOWNNAME'] . $f['properties']['VILLNAME']] = $f['properties']['VILLCODE'];
}

$weekJson = json_decode(file_get_contents($basePath . '/docs/json/weekList.json'), true);
$result = array(
  'meta' => array(
    'units' => array(),
  ),
);
$latestYm = 0;
foreach ($weekJson as $week) {
  if ($latestYm < $week['ym']) {
    $latestYm = $week['ym'];
  }
  $y = substr($week['ym'], 0, 4);
  $ymFile = $basePath . '/raw/' . $y . '/' . $week['ym'] . '.json';
  $ymJson = json_decode(file_get_contents($ymFile), true);
  foreach ($ymJson as $point) {
    if (empty($point['InvestigateUnit'])) {
      continue;
    }
    $key = $point['District'] . $point['Village'];
    if (isset($cunliPool[$key])) {
      if (!isset($result[$cunliPool[$key]])) {
        $result[$cunliPool[$key]] = array();
      }
      if (!isset($result[$cunliPool[$key]][$week['ym']])) {
        $result[$cunliPool[$key]][$week['ym']] = array();
      }
      if (!isset($result['meta']['units'][$point['InvestigateUnit']])) {
        $result['meta']['units'][$point['InvestigateUnit']] = $point['InvestigateUnit'];
      }
      if (!isset($result[$cunliPool[$key]][$week['ym']][$point['InvestigateUnit']])) {
        $result[$cunliPool[$key]][$week['ym']][$point['InvestigateUnit']] = array(
          'countPlus' => 0,
          'countEggs' => 0,
          'countTotal' => 0,
        );
      }
      if ($point['TotalEggs'] > 0) {
        $result[$cunliPool[$key]][$week['ym']][$point['InvestigateUnit']]['countPlus']++;
        $result[$cunliPool[$key]][$week['ym']][$point['InvestigateUnit']]['countEggs'] += $point['TotalEggs'];
      }
      ++$result[$cunliPool[$key]][$week['ym']][$point['InvestigateUnit']]['countTotal'];
    }
  }
}
$result['meta']['latest'] = $latestYm;
file_put_contents($basePath . '/docs/json/count.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
