<?php

$list = 'http://videos-collection.disney.fr';
$content = file_get_contents($list);

$html = new DOMDocument();
@$html->loadHTML($content);

$divFilmWrapper = null;
$nodeListDiv = $html->getElementsByTagName('div');
for ($i = 0; $i < ($nodeListDiv->length) ; $i++) {
  /* @var $elementDiv DOMElement */
  $elementDiv = $nodeListDiv->item($i);
  if (!$elementDiv->hasAttribute('class') || 'film_wrapper' != $elementDiv->getAttribute('class')) {
    continue;
  }
  $divFilmWrapper = $elementDiv;
}

if (is_null($divFilmWrapper)) { throw new Exception('no <div class="film_wrapper"> tag, Disney website has change!'); }

$divFilmWrapperUl = null;
for ($i = 0; $i < ($divFilmWrapper->childNodes->length) ; $i++) {
  /* @var $elements DOMElement */
  $element = $divFilmWrapper->childNodes->item($i);

  if (!is_a($element, 'DOMElement') || 'ul' != $element->tagName) {
    continue;
  }
  $divFilmWrapperUl = $element;
}

if (is_null($divFilmWrapperUl)) { throw new Exception('no <ul> tag in <div class="film_wrapper">, Disney website has change!'); }

$nodeListLi = $divFilmWrapperUl->getElementsByTagName('li');
for ($i = 0; $i < ($nodeListLi->length) ; $i++) {
  /* @var $elementLi DOMElement */
  $elementLi = $nodeListLi->item($i);
  $info = array(
    'title'       => $elementLi->hasAttribute('title') ? $elementLi->getAttribute('title') : '',
    'data-title'  => $elementLi->hasAttribute('data-title') ? $elementLi->getAttribute('data-title') : '',
    'data-link'   => $elementLi->hasAttribute('data-link') ? $elementLi->getAttribute('data-link') : '',
    'data-hash'   => $elementLi->hasAttribute('data-hash') ? $elementLi->getAttribute('data-hash') : '',
    'style'       => $elementLi->hasAttribute('style') ? $elementLi->getAttribute('style') : '',
    'img-link'    => $elementLi->hasAttribute('style') ? findImgLink($elementLi->getAttribute('style')) : ''
  );

  // TODO find number and decide if it's collection movie.
  if (!empty($info['data-link'])) {
    $htmlData = new DOMDocument();
    $contentData = file_get_contents($info['data-link']);
    @$htmlData->loadHTML($contentData);

    $nodeListDivData = $htmlData->getElementsByTagName('div');

    for ($j = 0; $j < ($nodeListDivData->length) ; $j++) {
      /* @var $elementDivData DOMElement */
      $elementDivData = $nodeListDivData->item($j);
      if (!$elementDivData->hasAttribute('class') || 'nbr_collection cl_dvd' != $elementDivData->getAttribute('class')) {
        continue;
      }
      $info['number'] = $elementDivData->textContent;
    }
    unset ($contentData, $nodeListDivData, $htmlData);
  }

  if (!empty($info['data-hash']) && !isset($info['number'])) { $info['number'] = manageNumberIssue($info['data-hash']); }

  var_dump($info);
}


/**
 *
 * @param string $PV_style : "background-image:url({URL});"
 * @return
 */
function findImgLink($PV_style) {
  preg_match('/^background-image:url\(([A-Za-z0-9:_\-\.\/]{1,})\);$/', $PV_style, $matches);
  if (isset($matches[1])) { return $matches[1]; }
  return '';
}

function manageNumberIssue($PV_dataHash) {
  switch ($PV_dataHash) {
    case 'planes' : return '108';
    case 'clochette-et-la-fee-pirate' : return '110';
    case 'planes-2' : return '111';
    default : throw new Exception('new movie without number : '.$PV_dataHash);
  }
}