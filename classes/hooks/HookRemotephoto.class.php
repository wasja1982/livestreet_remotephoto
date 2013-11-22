<?php
/**
 * Remote Photo - загрузка изображений в фотосет по ссылке
 *
 * Версия:	1.0.0
 * Автор:	Александр Вереник
 * Профиль:	http://livestreet.ru/profile/Wasja/
 * GitHub:	https://github.com/wasja1982/livestreet_remotephoto
 *
 **/

class PluginRemotephoto_HookRemotephoto extends Hook {
    public function RegisterHook() {
        $this->AddHook('template_form_add_topic_photoset_end','InjectLink');
    }

    public function InjectLink($aParam) {
        $sTemplatePath = Plugin::GetTemplatePath(__CLASS__) . 'inject_link.tpl';
        if ($this->Viewer_TemplateExists($sTemplatePath)) {
            return $this->Viewer_Fetch($sTemplatePath);
        }
    }
}