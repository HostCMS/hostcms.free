<?php

return array(
	'skin' => '"moono-lisa"',
	'enterMode' => 'CKEDITOR.ENTER_P',
	'forceEnterMode' => true,
	'extraPlugins' => '"stylesheetparser,uploadimage"',
	'removeButtons' => '"Save,NewPage,ExportPdf,Print,Templates,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Language"',
	'filebrowserUploadMethod' => '"form"',
	'filebrowserBrowseUrl' => 'hostcmsBackend + "/wysiwyg/filemanager/index.php?additionalFields=CKEditor,CKEditorFuncNum"',
	'uploadUrl' => 'hostcmsBackend + "/wysiwyg/upload.php"'
);