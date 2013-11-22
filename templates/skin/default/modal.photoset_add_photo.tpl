{**
 * Загрузка изображения
 *
 * @styles css/modals.css
 *}

{extends file='modals/modal_base.tpl'}

{block name='modal_id'}modal-photoset-upload{/block}
{block name='modal_class'}js-modal-default{/block}
{block name='modal_title'}{$aLang.uploadimg}{/block}

{block name='modal_header_after'}
    <ul class="nav nav-pills nav-pills-tabs" data-type="tabs">
		<li data-type="tab" data-option-target="tab-photoset-upload-pc" class="active"><a href="#">{$aLang.uploadimg_from_pc}</a></li>
		<li data-type="tab" data-option-target="tab-photoset-upload-link"><a href="#">{$aLang.uploadimg_from_link}</a></li>
	</ul>
{/block}

{block name='modal_content_after'}
    <form id="photoset-upload-form" method="POST" enctype="multipart/form-data" onsubmit="return false;" >
    <div data-type="tab-content">

        <div id="tab-photoset-upload-pc" class="tab-pane" data-type="tab-pane" style="display: block">
            <div class="modal-content">
                <label>{$aLang.topic_photoset_choose_image}:</label>
                <input type="file" id="photoset-upload-file" name="Filedata" />

                <input type="hidden" name="is_iframe" value="true" />
                <input type="hidden" name="topic_id" value="{$_aRequest.topic_id}" />
            </div>
        </div>

        <div id="tab-photoset-upload-link" class="tab-pane" data-type="tab-pane" >
            <div class="modal-content">
                <label for="photoset-upload-link">{$aLang.uploadimg_url}:</label>
                <input type="text" name="photoset-upload-link" id="photoset-upload-link" value="http://" class="input-text input-width-full" />
            </div>
        </div>

	</div>
    </form>

{/block}

{block name='modal_footer_begin'}
    <button type="submit" class="button button-primary" onclick="ls.photoset.upload();">{$aLang.topic_photoset_upload_choose}</button>
{/block}