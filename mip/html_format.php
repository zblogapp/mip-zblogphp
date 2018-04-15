<?php
require_once dirname(__FILE__) . '/simple_html_dom.php';

function mip_html_format ($content, $style = array()) {
  $dom = new simple_html_dom();
  $dom->load($content);
  remove_forbid_tags($dom);
  format_tags($dom);
  format_html_styles($dom, $style);

  if ($GLOBALS['mip_allow_self_theme_start']) {
    fix_incorrect_url($dom);
  }

  // remove styles
  return array(
    'text' => $dom->innertext,
    'style' => $style
  );
}

function fix_incorrect_url ($dom) {
  $urlAttrs = array('src', 'href');
  foreach ($urlAttrs as $attr) {
    $items = $dom->find("[$attr]");
    foreach ($items as $item) {
      $item->$attr = str_replace('mip/', '', $item->$attr);
    }
  }
}

function remove_forbid_tags ($dom) {
  $replaceArray = array('img', 'video', 'audio', 'iframe', 'form');
  $removeArray = array('frame', 'frameset', 'object', 'param', 'applet', 'embed', 'style');
  foreach ($replaceArray as $tag) {
    $items = $dom->find($tag);
    foreach ($items as $item) {
      $item->tag = 'mip-' . $tag;
      if ($tag == 'form' && $item->action) {
        $item->url = $item->action;
        $item->action = null;
      }
    }
  }
  foreach ($removeArray as $tag) {
    $items = $dom->find($tag);
    foreach ($items as $item) {
      $item->tag = 'p';
    }
  }
}

function stylearray_to_css ($style) {
  $ret = array('');
  foreach ($style as $key => $value) {
    $ret[] = '.' . $key . '{' . $value . '}';
  }
  return implode('', $ret);
}

function format_tags ($dom) {
  $styles = array(
    'em' => 'font-style:italic;',
    'strong' => 'font-weight:bold;',
    'b' => 'font-weight:bold;',
    'sup' => 'vertical-align:super;font-size:smaller;',
    'sub' => 'vertical-align:sub;font-size: smaller;'
  );
  foreach ($styles as $tag => $style) {
    $items = $dom->find($tag);
    foreach ($items as $item) {
      $item->tag = 'span';
      if ($item->style) {
        $item->style .= ';' . $style;
      } else {
        $item->style = $style;
      }
    }
  }
}

function format_html_styles ($dom, &$style) {
  $elements = $dom->find('[style]');
  foreach ($elements as $element) {
    $styleName = modified_hash($element->style);
    $style[$styleName] = $element->style;
    $element->style = null;
    if ($element->class) {
      $element->class .= ' ' . $styleName;
    } else {
      $element->class = $styleName;
    }
  }
  return $dom;
}

function modified_hash ($text) {
  $text = md5($text);
  return 's' . substr($text, 0, 6);
}
