
<form method="post" action="<?php option('uri'); ?>tag-add.php">
<fieldset id="addtag">
Add tag: <input name="tag" type="text" id="tag" size="10" maxlength="30" /> 
<input type="hidden" name="id" value="<?php topic_id(); ?>" />
<input type="submit" name="Submit" value="Add" />
</fieldset>
</form>
