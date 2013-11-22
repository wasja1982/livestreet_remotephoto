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

class PluginRemotephoto_ActionPhotoset extends PluginRemotephoto_Inherit_ActionPhotoset {
    /**
     * AJAX загрузка фоток
     *
     * @return unknown
     */
    protected function EventUpload() {
        if (!empty($_FILES['Filedata']['tmp_name'])) {
            return parent::EventUpload();
        }
        /**
         * Устанавливаем формат Ajax ответа
         * В зависимости от типа загрузчика устанавливается тип ответа
         */
        if (getRequest('is_iframe')) {
            $this->Viewer_SetResponseAjax('jsonIframe', false);
        } else {
            $this->Viewer_SetResponseAjax('json');
        }
        /**
         * Проверяем авторизован ли юзер
         */
        if (!$this->User_IsAuthorization()) {
            $this->Message_AddErrorSingle($this->Lang_Get('not_access'),$this->Lang_Get('error'));
            return Router::Action('error');
        }
        /**
         * Файл был загружен?
         */
        if (!isPost('photoset-upload-link') || !is_string(getRequest('photoset-upload-link')) ||
            !preg_match('/^https?:\/\/.+/i', getRequest('photoset-upload-link'))) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return false;
        }

        $iTopicId = getRequestStr('topic_id');
        $sTargetId = null;
        $iCountPhotos = 0;
        // Если от сервера не пришёл id топика, то пытаемся определить временный код для нового топика. Если и его нет. то это ошибка
        if (!$iTopicId) {
            $sTargetId = empty($_COOKIE['ls_photoset_target_tmp']) ? getRequestStr('ls_photoset_target_tmp') : $_COOKIE['ls_photoset_target_tmp'];
            if (!$sTargetId) {
                $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                return false;
            }
            $iCountPhotos = $this->Topic_getCountPhotosByTargetTmp($sTargetId);
        } else {
            /**
             * Загрузка фото к уже существующему топику
             */
            $oTopic = $this->Topic_getTopicById($iTopicId);
            if (!$oTopic or !$this->ACL_IsAllowEditTopic($oTopic,$this->oUserCurrent)) {
                $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                return false;
            }
            $iCountPhotos = $this->Topic_getCountPhotosByTopicId($iTopicId);
        }
        /**
         * Максимальное количество фото в топике
         */
        if ($iCountPhotos >= Config::Get('module.topic.photoset.count_photos_max')) {
            $this->Message_AddError($this->Lang_Get('topic_photoset_error_too_much_photos', array('MAX' => Config::Get('module.topic.photoset.count_photos_max'))), $this->Lang_Get('error'));
            return false;
        }
        $sUrl = getRequest('photoset-upload-link');
        /**
         * Проверяем, является ли файл изображением
         */
        if(!@getimagesize($sUrl)) {
            $this->Message_AddErrorSingle($this->Lang_Get('uploadimg_url_error_type'),$this->Lang_Get('error'));
            return false;
        }
        /**
         * Открываем файловый поток и считываем файл поблочно,
         * контролируя максимальный размер изображения
         */
        $oFile=fopen($sUrl,'r');
        if(!$oFile) {
            $this->Message_AddErrorSingle($this->Lang_Get('uploadimg_url_error_read'),$this->Lang_Get('error'));
            return false;
        }

        $iMaxSizeKb=Config::Get('view.img_max_size_url');
        $iSizeKb=0;
        $sContent='';
        while (!feof($oFile) and $iSizeKb<$iMaxSizeKb) {
            $sContent.=fread($oFile ,1024*1);
            $iSizeKb++;
        }
        /**
         * Если конец файла не достигнут,
         * значит файл имеет недопустимый размер
         */
        if(!feof($oFile)) {
            $this->Message_AddError($this->Lang_Get('topic_photoset_error_bad_filesize', array('MAX' => Config::Get('view.img_max_size_url'))), $this->Lang_Get('error'));
            return false;
        }
        fclose($oFile);

        /**
         * Создаем файл для хранения изображения
         */
        $sFileName = func_generator(10);
        $sPath = Config::Get('path.uploads.images').'/topic/'.date('Y/m/d').'/';

        if (!is_dir(Config::Get('path.root.server').$sPath)) {
            mkdir(Config::Get('path.root.server').$sPath, 0755, true);
        }

        $sFileTmp = Config::Get('path.root.server').$sPath.$sFileName;

        $fp=fopen($sFileTmp,'w');
        fwrite($fp,$sContent);
        fclose($fp);

        /**
         * Максимальный размер фото
         */
        if (filesize($sFileTmp) > Config::Get('module.topic.photoset.photo_max_size')*1024) {
            $this->Message_AddError($this->Lang_Get('topic_photoset_error_bad_filesize', array('MAX' => Config::Get('module.topic.photoset.photo_max_size'))), $this->Lang_Get('error'));
            @unlink($sFileTmp);
            return false;
        }

        $aParams=$this->Image_BuildParams('photoset');

        $oImage =$this->Image_CreateImageObject($sFileTmp);
        /**
         * Если объект изображения не создан,
         * возвращаем ошибку
         */
        if($sError=$oImage->get_last_error()) {
            // Вывод сообщения об ошибки, произошедшей при создании объекта изображения
            $this->Message_AddError($sError,$this->Lang_Get('error'));
            @unlink($sFileTmp);
            return false;
        }
        /**
         * Превышает максимальные размеры из конфига
         */
        if (($oImage->get_image_params('width')>Config::Get('view.img_max_width')) or ($oImage->get_image_params('height')>Config::Get('view.img_max_height'))) {
            $this->Message_AddError($this->Lang_Get('topic_photoset_error_size'),$this->Lang_Get('error'));
            @unlink($sFileTmp);
            return false;
        }
        /**
         * Добавляем к загруженному файлу расширение
         */
        $sFile=$sFileTmp.'.'.$oImage->get_image_params('format');
        rename($sFileTmp,$sFile);

        $aSizes=Config::Get('module.topic.photoset.size');
        foreach ($aSizes as $aSize) {
            /**
             * Для каждого указанного в конфиге размера генерируем картинку
             */
            $sNewFileName = $sFileName.'_'.$aSize['w'];
            $oImage = $this->Image_CreateImageObject($sFile);
            if ($aSize['crop']) {
                $this->Image_CropProportion($oImage, $aSize['w'], $aSize['h'], true);
                $sNewFileName .= 'crop';
            }
            $this->Image_Resize($sFile,$sPath,$sNewFileName,Config::Get('view.img_max_width'),Config::Get('view.img_max_height'),$aSize['w'],$aSize['h'],true,$aParams,$oImage);
        }
        /*
         * Проверка на использование плагина "Domain for static"
         */
        if (class_exists('PluginStaticdomain') || class_exists('PluginSelectelStorage')) {
            $plugins = $this->Plugin_GetActivePlugins();
            if (in_array('staticdomain', $plugins)) {
                $sFilePathOld = $sFile;
                $sServer = rtrim(str_replace(DIRECTORY_SEPARATOR,'/',Config::Get('path.root.server')),'/');
                $sStatic = rtrim(str_replace(DIRECTORY_SEPARATOR,'/',Config::Get('plugin.staticdomain.static_server')),'/');
                $sFile = str_replace($sServer . '/', $sStatic . '/', $sFilePathOld);
                @rename(str_replace('/', DIRECTORY_SEPARATOR, $sFilePathOld), str_replace('/', DIRECTORY_SEPARATOR, $sFile));
            } elseif (in_array('selectelstorage', $plugins)) {
                $sFile = $this->PluginSelectelStorage_Image_UploadToSelectelStorage($sFile);
            }
        }
        $sFile = $this->Image_GetWebPath($sFile);

        if ($sFile) {
            /**
             * Создаем фото
             */
            $oPhoto = Engine::GetEntity('Topic_TopicPhoto');
            $oPhoto->setPath($sFile);
            if ($iTopicId) {
                $oPhoto->setTopicId($iTopicId);
            } else {
                $oPhoto->setTargetTmp($sTargetId);
            }
            if ($oPhoto = $this->Topic_addTopicPhoto($oPhoto)) {
                /**
                 * Если топик уже существует (редактирование), то обновляем число фоток в нём
                 */
                if (isset($oTopic)) {
                    $oTopic->setPhotosetCount($oTopic->getPhotosetCount()+1);
                    $this->Topic_UpdateTopic($oTopic);
                }

                $this->Viewer_AssignAjax('file', $oPhoto->getWebPath('100crop'));
                $this->Viewer_AssignAjax('id', $oPhoto->getId());
                $this->Message_AddNotice($this->Lang_Get('topic_photoset_photo_added'), $this->Lang_Get('attention'));
            } else {
                $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                return false;
            }
        } else {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return false;
        }
    }
}
?>