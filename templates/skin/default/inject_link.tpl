<script type="text/javascript">
    jQuery(document).ready(function($) {
        $("#photoset-start-upload").first()
                .attr('onclick','jQuery(\'[data-option-target=tab-photoset-upload-pc]\').tab(\'activate\');')
                .after('
<a href="#" onclick="jQuery(\'[data-option-target=tab-photoset-upload-link]\').tab(\'activate\');" id="photoset-start-upload-link" data-type="modal-toggle" data-option-target="modal-photoset-upload" class="link-dotted">{$aLang.plugin.remotephoto.add_link_text}</a><br />');

        ls.hook.inject([ls.photoset,'upload'], 'if ($("#tab-photoset-upload-pc").isVisible().is(":visible")) { $("#photoset-upload-file").val("");} else { $("#photoset-upload-link").val("http://"); }');
    });
</script>