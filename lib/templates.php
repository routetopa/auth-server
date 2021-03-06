<?php

require_once('Smarty.class.php');

class SmartyT extends Smarty {

    function getStrings($lang, $file) {
        $strings = (@include dirname(__FILE__) . "/lang/{$lang}/{$file}.php");
        if (!$strings && $lang != 'en') { $strings = (@include dirname(__FILE__) . "/lang/en/{$stringsfile}.php"); }
        return $strings ?: [];
    }

    function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
    {
        // Retrieve language from session, use 'en' as default
        $lang = @$_SESSION['lang'] ?: 'en';
        $strings = $this->getStrings($lang, 'common');
        // Get translations filename from template's filename
        $file = substr($template, 0, strrpos($template, '.'));
        // Try loading translation, or use 'en' as fallback
        $strings = array_merge($strings, $this->getStrings($lang, $file));
        $this->assign('current_lang', $lang);
        $this->assign('l', $strings);
        // Default menu id not present
        if (!$this->getTemplateVars('right_menu')) {
            $this->assign('right_menu', build_menu());
        }
        // Render template
        return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
    }

}

function getSmarty() {
    $libPath = dirname(__FILE__); //. DIRECTORY_SEPARATOR . 'lib';
    $s = new SmartyT();
    $s->setTemplateDir($libPath . DIRECTORY_SEPARATOR . 'templates');
    $s->setCompileDir($libPath . DIRECTORY_SEPARATOR . 'templates_c');
    $s->setCacheDir($libPath . DIRECTORY_SEPARATOR . 'templates_cache');
    $s->setConfigDir($libPath . DIRECTORY_SEPARATOR . 'templates_conf');
    $s->addPluginsDir($libPath . DIRECTORY_SEPARATOR . 'templates_plug');
    return $s;
}