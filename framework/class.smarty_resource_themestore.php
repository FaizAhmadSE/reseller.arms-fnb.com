<?php
// smarty themestore resources plugin
// for handling {ts:xxx.tpl} path
class Smarty_Resource_Themestore extends Smarty_Resource_Custom
{

function fetch($tpl_name, &$source, &$time)
{
        $template = $this->smarty->template_dir[0] . '/' . $tpl_name;

        if (isset($_SESSION['themepreview']))
        {
                global $config;
                $path = $config['themestore_path'].$_SESSION['themepreview']['url'].basename($tpl_name);
                if (file_exists($path)) $template = $path;
        }
        $source = file_get_contents($template);
        $time = filemtime($template);
}

function fetchTimestamp($tpl_name)
{
        if (isset($_SESSION['themepreview']))
        {
            return time(); //always refresh
        }
        $template = $this->smarty->template_dir[0] . '/' . $tpl_name;
        return filemtime($template);

}

 public function populate(Smarty_Template_Source $source, Smarty_Internal_Template $_template=null)
    {
        $this->smarty = $source->smarty;
        parent::populate($source, $_template);
    }

}
?>
