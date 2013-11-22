<script type="text/javascript">
    jQuery(document).ready(function($) {
        jQuery("#swfu_images").first().after('<a href="#" onclick="ls.photoset.showFormLink(); return false;" id="photoset-start-upload-link">{$aLang.plugin.remotephoto.add_link_text}</a><br />');
        $('#photoset-upload-form').jqmAddTrigger('#photoset-start-upload-link');
        ls.photoset.showForm = function() {
            ls.blocks.switchTab('pc', 'upload-photo');
            $('#photoset-upload-form').show();
        }
        ls.photoset.showFormLink = function() {
            ls.blocks.switchTab('link', 'upload-photo');
            $('#photoset-upload-form').show();
        }

        $('#photoset-upload-form .modal-content').first().prepend('<ul class="nav nav-pills nav-pills-tabs"><li class="active js-block-upload-photo-item" data-type="pc"><a href="#">{$aLang.uploadimg_from_pc}</a></li><li class="js-block-upload-photo-item" data-type="link"><a href="#">{$aLang.uploadimg_from_link}</a></li></ul><div id="block_upload_photo_content_link" class="tab-content js-block-upload-photo-content" data-type="link" style="display: none;"><label for="photoset-upload-link">{$aLang.uploadimg_url}:</label><input type="text" name="photoset-upload-link" id="photoset-upload-link" value="http://" class="input-text input-width-full" /></div>');
        $('#photoset-upload-form .modal-content > label, #photoset-upload-form .modal-content > input').wrap('<div id="block_upload_photo_content_pc" class="tab-content js-block-upload-photo-content" data-type="pc"></div>');
        ls.blocks.initSwitch('upload-photo');
        ls.hook.inject([ls.photoset,'upload'], '{literal}if ($("#block_upload_photo_content_link").is(":visible")) {$("#photoset-upload-file").val("");} else {$("#photoset-upload-link").val("http://");}{/literal}');

        $('#photoset-upload-file').addClass('input-text input-width-full');
    });
</script>
