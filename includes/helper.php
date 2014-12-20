<?php 
function delete($path) {
	$directory = $path;

	if(is_file($directory)){
		// its a file, remove it!
		@unlink($directory);
	}else{
		if(substr($directory,-1) == "/") {
			$directory = substr($directory,0,-1);
		}

		if(!file_exists($directory) || !is_dir($directory)) {
			return false;
		} elseif(!is_readable($directory)) {
			return false;
		} else {
			$directoryHandle = opendir($directory);

			while ($contents = readdir($directoryHandle)) {
				if($contents != '.' && $contents != '..') {
					$path = $directory . "/" . $contents;

					if(is_dir($path)) {
						delete($path);
					} else {
						unlink($path);
					}
				}
			}

			closedir($directoryHandle);
			if(!rmdir($directory)) {
				return false;
			}

			return true;
		}
	}
}

// Copy one dir over another
function full_copy($source, $target){
	if (is_dir($source)){
		if (!is_dir($target))  mkdir($target, null, true);
		$d = dir($source);

		while (FALSE !== ($entry = $d->read())){
			if ($entry == '.' || $entry == '..' || $entry =='.git'){
				continue;
			}

			$Entry = $source . '/' . $entry;
			if (is_dir( $Entry )){
				full_copy($Entry, $target . '/' . $entry);
				continue;
			}
			copy($Entry, $target . '/' . $entry);
			if (!is_file($target . '/' . $entry)) return false;
		}
		$d->close();
	} else {
		copy($source, $target);
		if (!is_file($target)) return false;
	}

	return true;
}

function notify($strMessage, $strTitle) {
	global $docready;
	
	$docready.= '
	$("#container").notify("create", {
		title: \''.$strTitle.'\',
		text: \''.str_replace("'", "\'", $strMessage).'\'
	});
	';
}

?>