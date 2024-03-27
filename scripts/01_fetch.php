<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

$browser = new HttpBrowser(HttpClient::create());

if (date('N') == 1) {
  $timeEnd = strtotime(date('Y-m-d') . ' 00:00:00') - 1;
} else {
  $timeEnd = strtotime('last monday') - 1;
}

$n = 30;
$firstFileDone = false;
$weekList = array();

while (--$n > 0) {
  $timeBegin = strtotime('last monday', $timeEnd);

  $yearPath = dirname(__DIR__) . '/raw/' . date('Y', $timeEnd);
  if (!file_exists($yearPath)) {
    mkdir($yearPath, 0777, true);
  }
  $targetFile = $yearPath . '/' . date('oW', $timeEnd) . '.json';
  if (false === $firstFileDone) {
    $firstFileDone = true;
    if (file_exists($targetFile)) {
      unlink($targetFile);
    }
  }

  if (!file_exists($targetFile)) {
    $strBegin = urlencode(date('Y/m/d', $timeBegin));
    $strEnd = urlencode(date('Y/m/d', $timeEnd));

    $city = urlencode('台南市');
    $browser->request('GET', 'https://tainan-ovitrap.nmbdcrc.tw');
    $headers = $browser->getResponse()->getHeaders();
    $browser->request('GET', "https://ovitrap-api.azurewebsites.net/DistributionRecord?StartTime={$strBegin}&EndTime={$strEnd}&City={$city}&District=0&Village=&InvestUnitIds=2%2C4%2C5%2C9%2C13&Outdoor=0");
    $data1 = json_decode($browser->getResponse()->getContent(), true);

    // $city = urlencode('高雄市');
    // $browser->request('GET', 'https://tainan-ovitrap.nmbdcrc.tw');
    // $browser->request('GET', "https://ovitrap-api.azurewebsites.net/DistributionRecord?StartTime={$strBegin}&EndTime={$strEnd}&City={$city}&District=0&Village=&InvestUnitIds=2%2C4%2C5%2C9%2C13&Outdoor=0");
    // $data2 = json_decode($browser->getResponse()->getContent(), true);

    file_put_contents($targetFile, json_encode($data1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  }
  $weekList[] = array(
    'ym' => date('oW', $timeEnd),
    'begin' => $timeBegin,
    'end' => $timeEnd,
  );

  $timeEnd = $timeBegin - 1;
}

file_put_contents(dirname(__DIR__) . '/raw/weekList.json', json_encode($weekList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
