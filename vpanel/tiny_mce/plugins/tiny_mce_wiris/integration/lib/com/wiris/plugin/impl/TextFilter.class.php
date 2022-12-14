<?php

class com_wiris_plugin_impl_TextFilter {
	public function __construct($plugin) {
		if(!php_Boot::$skip_constructor) {
		$this->plugin = $plugin;
		$this->render = $plugin->newRender();
		$this->fixUrl = null;
	}}
	public function save_xml_encode($str) {
		$tags = com_wiris_plugin_impl_TextFilterTags::newSafeXml();
		$str = str_replace($tags->out_double_quote, $tags->in_double_quote, $str);
		$str = str_replace($tags->out_open, $tags->in_open, $str);
		$str = str_replace($tags->out_close, $tags->in_close, $str);
		$str = str_replace($tags->out_entity, $tags->in_entity, $str);
		$str = str_replace($tags->out_quote, $tags->in_quote, $str);
		return $str;
	}
	public function html_entity_encode($str) {
		$str = str_replace("<", "&lt;", $str);
		$str = str_replace(">", "&gt;", $str);
		$str = str_replace("\"", "&quot;", $str);
		$str = str_replace("&", "&amp;", $str);
		return $str;
	}
	public function html_entity_decode($str) {
		$str = str_replace("&lt;", "<", $str);
		$str = str_replace("&gt;", ">", $str);
		$str = str_replace("&quot;", "\"", $str);
		$str = str_replace("&nbsp;", com_wiris_plugin_impl_TextFilter::$NBSP, $str);
		$str = str_replace("&amp;", "&", $str);
		return $str;
	}
	public function math2Img($str, $prop) {
		$img = "<img";
		$output = array();;
		$prop["centerbaseline"] = "false";
		$prop["accessible"] = "true";
		$prop["metrics"] = "true";
		$src = $this->render->createImage($str, $prop, $output);
		$img .= " src=\"" . $src . "\"";
		$alt = com_wiris_system_PropertiesTools::getProperty($output, "alt", null);
		$width = com_wiris_system_PropertiesTools::getProperty($output, "width", null);
		$height = com_wiris_system_PropertiesTools::getProperty($output, "height", null);
		$baseline = com_wiris_system_PropertiesTools::getProperty($output, "baseline", null);
		$dpi = Std::parseFloat($this->plugin->getConfiguration()->getProperty(com_wiris_plugin_api_ConfigurationKeys::$WIRIS_DPI, "96"));
		$mml = $this->plugin->getConfiguration()->getProperty(com_wiris_plugin_api_ConfigurationKeys::$FILTER_OUTPUT_MATHML, "false") === "true";
		$f = 96 / $dpi;
		$dwidth = $f * Std::parseFloat($width);
		$dheight = $f * Std::parseFloat($height);
		$dbaseline = $f * Std::parseFloat($baseline);
		$alt = $this->html_entity_encode($alt);
		$img .= " class=\"Wirisformula\"";
		$img .= " alt=\"" . $alt . "\"";
		$img .= " width=\"" . _hx_string_rec($dwidth, "") . "\"";
		$img .= " height=\"" . _hx_string_rec($dheight, "") . "\"";
		$d = $dbaseline - $dheight;
		$img .= " style=\"vertical-align:" . _hx_string_rec($d, "") . "px\"";
		if($mml) {
			$tag = $this->plugin->getConfiguration()->getProperty(com_wiris_plugin_api_ConfigurationKeys::$EDITOR_MATHML_ATTRIBUTE, "data-mathml");
			$img .= " " . $tag . "=\"" . $this->save_xml_encode($str) . "\"";
		}
		$img .= "/>";
		return $img;
	}
	public function filterApplet($tags, $text, $prop, $safeXML) {
		$n0 = null;
		$n1 = null;
		$output = null;
		$sub = null;
		$output = new StringBuf();
		$n0 = 0;
		$n1 = _hx_index_of($text, $tags->in_appletopen, $n0);
		while($n1 >= 0) {
			$output->add(_hx_substr($text, $n0, $n1 - $n0));
			$n0 = $n1;
			$n1 = _hx_index_of($text, $tags->in_appletclose, $n0);
			if($n1 >= 0) {
				$n1 = $n1 + strlen($tags->in_appletclose);
				$sub = _hx_substr($text, $n0, $n1 - $n0);
				if($safeXML) {
					if($this->fixUrl === null) {
						$this->fixUrl = new EReg("<a href=\"[^\"]*\"[^>]*>([^<]*)<\\/a>|<a href=\"[^\"]*\">", "");
					}
					$sub = $this->fixUrl->replace($sub, "\$1");
					$sub = $this->html_entity_decode($sub);
					$sub = str_replace($tags->in_double_quote, $tags->out_double_quote, $sub);
					$sub = str_replace($tags->in_open, $tags->out_open, $sub);
					$sub = str_replace($tags->in_close, $tags->out_close, $sub);
					$sub = str_replace($tags->in_entity, $tags->out_entity, $sub);
					$sub = str_replace($tags->in_quote, $tags->out_quote, $sub);
				}
				$n0 = $n1;
				$output->add($sub);
				$n1 = _hx_index_of($text, $tags->in_appletopen, $n0);
			}
		}
		$output->add(_hx_substr($text, $n0, null));
		return $output->b;
	}
	public function filterMath($tags, $text, $prop, $safeXML) {
		$n0 = null;
		$n1 = null;
		$output = null;
		$sub = null;
		$output = new StringBuf();
		$n0 = 0;
		$n1 = _hx_index_of($text, $tags->in_mathopen, $n0);
		while($n1 >= 0) {
			$output->add(_hx_substr($text, $n0, $n1 - $n0));
			$n0 = $n1;
			$n1 = _hx_index_of($text, $tags->in_mathclose, $n0);
			if($n1 >= 0) {
				$n1 = $n1 + strlen($tags->in_mathclose);
				$sub = _hx_substr($text, $n0, $n1 - $n0);
				if($safeXML) {
					if($this->fixUrl === null) {
						$this->fixUrl = new EReg("<a href=\"[^\"]*\"[^>]*>([^<]*)<\\/a>|<a href=\"[^\"]*\">", "");
					}
					$sub = $this->fixUrl->replace($sub, "\$1");
					$sub = $this->html_entity_decode($sub);
					$sub = str_replace($tags->in_double_quote, $tags->out_double_quote, $sub);
					$sub = str_replace($tags->in_open, $tags->out_open, $sub);
					$sub = str_replace($tags->in_close, $tags->out_close, $sub);
					$sub = str_replace($tags->in_entity, $tags->out_entity, $sub);
					$sub = str_replace($tags->in_quote, $tags->out_quote, $sub);
				}
				$sub = $this->math2Img($sub, $prop);
				$n0 = $n1;
				$output->add($sub);
				$n1 = _hx_index_of($text, $tags->in_mathopen, $n0);
			}
		}
		$output->add(_hx_substr($text, $n0, null));
		return $output->b;
	}
	public function filter($str, $prop) {
		$saveMode = null;
		if($prop !== null) {
			$saveMode = com_wiris_system_PropertiesTools::getProperty($prop, "savemode", null);
		}
		if($saveMode === null) {
			$saveMode = $this->plugin->getConfiguration()->getProperty(com_wiris_plugin_api_ConfigurationKeys::$SAVE_MODE, "xml");
		}
		$b = null;
		$b = $saveMode === "safeXml";
		$tags = null;
		if($b) {
			$tags = com_wiris_plugin_impl_TextFilterTags::newSafeXml();
		} else {
			$tags = com_wiris_plugin_impl_TextFilterTags::newXml();
		}
		$str = $this->filterMath($tags, $str, $prop, $b);
		$str = $this->filterApplet($tags, $str, $prop, $b);
		return $str;
	}
	public $fixUrl;
	public $render;
	public $plugin;
	public function __call($m, $a) {
		if(isset($this->$m) && is_callable($this->$m))
			return call_user_func_array($this->$m, $a);
		else if(isset($this->?dynamics[$m]) && is_callable($this->?dynamics[$m]))
			return call_user_func_array($this->?dynamics[$m], $a);
		else if('toString' == $m)
			return $this->__toString();
		else
			throw new HException('Unable to call ?'.$m.'?');
	}
	static $NBSP;
	function __toString() { return 'com.wiris.plugin.impl.TextFilter'; }
}
com_wiris_plugin_impl_TextFilter::$NBSP = com_wiris_plugin_impl_TextFilter_0();
function com_wiris_plugin_impl_TextFilter_0() {
	{
		$s = new haxe_Utf8(null);
		$s->addChar(160);
		return $s->toString();
	}
}
