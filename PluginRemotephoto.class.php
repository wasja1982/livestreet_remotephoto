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

/**
 * Запрещаем напрямую через браузер обращение к этому файлу.
 */
if (! class_exists ( 'Plugin' )) {
    die ( 'Hacking attemp!' );
}

class PluginRemotephoto extends Plugin {

    public $aDelegates = array(
        'template' => array(
            'modals/modal.photoset_add_photo.tpl' => '_modal.photoset_add_photo.tpl',
        ),
    );

    protected $aInherits = array(
        'action' => array('ActionPhotoset')
    );

    /**
     * Активация плагина
     */
    public function Activate() {
        return true;
    }

    /**
     * Инициализация плагина
     */
    public function Init() {
        return true;
    }
}