<?php

return array(
	'skin' => '"moono-lisa"',
	'enterMode' => 'CKEDITOR.ENTER_P',
	'forceEnterMode' => true,
	'extraPlugins' => '"stylesheetparser,uploadimage"',
	'removeButtons' => '"Save,NewPage,ExportPdf,Print,Templates,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Language"',
    'filebrowserUploadMethod' => '"form"',
	'filebrowserBrowseUrl' => '"/admin/wysiwyg/filemanager/index.php?additionalFields=CKEditor,CKEditorFuncNum"',
	'uploadUrl' => '"/admin/wysiwyg/upload.php"'
);