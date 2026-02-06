<script>
<?php
/**
 * Filter main UI JS config.
 * @see fl_builder_ui_js_config
 */
echo 'FLBuilderConfig  = ' . FLBuilderConfig::get_json() . ';';


/**
 * Filter UI JS Strings.
 * @see fl_builder_ui_js_strings
 */
echo 'FLBuilderStrings = ' . FLBuilderConfig::get_strings_json() . ';';

FLBuilderFonts::js();

?>
</script>
