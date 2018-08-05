<?php
require_once dirname(__FILE__) . '/simple_html_dom.php';

class MIP_Formatter {
    /**
     * @var string[]
     */
    public $styles;
    /**
     * @var string
     */
    public $warnings;

    public function __construct($styles = array())
    {
        $this->styles = $styles;
    }
    /**
     * @param $content
     * @return string
     * @throws \Exception
     */
    public function format ($content) {
        $dom = new simple_html_dom();
        $dom->load($content);
        $this->removeForbidTags($dom);
        $this->formatTags($dom);
        $this->formatHtmlStyles($dom);
        if ($GLOBALS['mip_allow_self_theme_start']) {
            $this->fixIncorrentUrl($dom);
        }
        $html = $dom->innertext;
        return $html;
    }
    /**
     * Get CSS of proceed HTML
     * @return string
     */
    public function css () {
        $ret = array('');
        foreach ($this->styles as $key => $value) {
            $ret[] = '.' . $key . '{' . html_entity_decode($value) . '}';
        }
        $text = implode('', $ret);
        $text = TransferHTML($text, '[nohtml]');
        return $text;
    }
    /**
     * Fix incorrect urls
     * @param \simple_html_dom $dom
     */
    protected function fixIncorrentUrl ($dom) {
        $urlAttrs = array('src', 'href');
        foreach ($urlAttrs as $attr) {
            $items = $dom->find("[$attr]");
            foreach ($items as $item) {
                $item->$attr = mip_theme_get_original_url($item->$attr);
            }
        }
    }
    /**
     * Get hash of a style
     * @param $text
     * @return string
     */
    protected function styleHash ($text) {
        $text = md5($text);
        return 's' . substr($text, 0, 6);
    }
    /**
     * Extract all styles to one style
     * @param \simple_html_dom $dom
     * @return \simple_html_dom
     */
    protected function formatHtmlStyles ($dom) {
        $elements = $dom->find('[style]');
        foreach ($elements as $element) {
            $styleName = $this->styleHash($element->style);
            $style = preg_replace('/(width|height):.*?[;|$]/', '', $element->style);
            $this->styles[$styleName] = $style;
            $element->style = null;
            if ($element->class) {
                $element->class .= ' ' . $styleName;
            } else {
                $element->class = $styleName;
            }
        }
        return $dom;
    }

    protected function removeForbidTags ($dom) {
      $replaceArray = array('img', 'video', 'audio', 'iframe', 'form');
      $removeArray = array('frame', 'frameset', 'object', 'param', 'applet', 'embed', 'style', 'script');
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
          if ($item->tag == 'script') {
            if (preg_match('/c.mipcdn.com/si', $item->src)) {
              continue;
            }
          } else if ($item->tag == 'style') {
            if ($item->hasAttribute('mip-custom')) {
              continue;
            }
          }
          $item->tag = 'p';
        }
      }
    }

    /**
     * Convert some HTML tags to <span> with style
     * @param \simple_html_dom $dom
     */
    protected function formatTags ($dom) {
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

}
