<?php
/*	Project:	EQdkp-Plus
 *	Package:	Repo Helper
*	Link:		http://eqdkp-plus.eu
*
*	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
*
*	This program is free software: you can redistribute it and/or modify
*	it under the terms of the GNU Affero General Public License as published
*	by the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU Affero General Public License for more details.
*
*	You should have received a copy of the GNU Affero General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set('display_errors', 0);

include_once('includes/config.php');
include_once('includes/helper.php');
include_once('includes/Git.php');

function scanRepoDirectory(){
	global $repo_dir;
	
	$arrOut = array();
	$arrData = scandir($repo_dir);
	foreach($arrData as $key => $elem){
		if ($key == "." || $key == "..") continue;
		
		if (is_dir($repo_dir.'/'.$elem.'/.git')){
			$arrOut[$elem] = $elem;
		}
	}
	return $arrOut;
}

//Global Vars
$arrRepos = array();
$arrReposAtEQdkp = array();
$docready;

function bootstrap(){
	global $arrRepos, $arrReposAtEQdkp;
	
	$arrRepos = scanRepoDirectory();
	
	if (isset($_POST['repo']) && $_POST['repo'] != ""){
		handle_actions($_POST['repo']);
	}
	
	$arrReposAtEQdkp = check_if_repo_in_eqdkp();
}

function handle_actions($strRepo){
	if (isset($_POST['copytoeqdkp'])){
		copytoeqdkp($strRepo);
	}
	
	if (isset($_POST['copytorepo'])){
		copytorepo($strRepo);
	}
	
	if (isset($_POST['deletefromeqdkp'])){
		deletefromeqdkp($strRepo);
	}
	
	if (isset($_POST['gitupdate'])){
		gitupdate($strRepo);
	}
	
	if (isset($_POST['gitcommit'])){
		gitcommit($strRepo);
	}
	
	if (isset($_POST['gitpush'])){
		gitpush($strRepo);
	}
}

function gitupdate($strRepo){
	global $repo_dir, $eqdkp_dir, $git_path, $isWindows;
	try{
		if($isWindows) Git::windows_mode();
		Git::set_bin($git_path);
		
		$repoPath = ($strRepo == 'local_core') ? $eqdkp_dir : $repo_dir.$strRepo;
		$repo = Git::open($repoPath);
		notify($repo->status(true), 'Git Status before pull');
		$repo->pull("origin", "master");
		notify($repo->status(true), 'Git Status after pull');
	} catch(Exception $e){
		echo $e->getMessage();
	}
}

function gitcommit($strRepo){
	global $repo_dir, $eqdkp_dir, $git_path, $isWindows;
	try{
		if($isWindows) Git::windows_mode();
		Git::set_bin($git_path);
		$repoPath = ($strRepo == 'local_core') ? $eqdkp_dir : $repo_dir.$strRepo;
		$repo = Git::open($repoPath);
		notify($repo->status(true), 'Git Status before commit');
		$repo->add('.');
		$repo->commit($_POST['gitcommitmsg']);
		notify($repo->status(true), 'Git Status after commit');
	} catch(Exception $e){
		echo $e->getMessage();
	}
}

function gitpush($strRepo){
	global $repo_dir, $eqdkp_dir, $git_path, $isWindows;
	try{
		if($isWindows) Git::windows_mode();
		Git::set_bin($git_path);
		$repoPath = ($strRepo == 'local_core') ? $eqdkp_dir : $repo_dir.$strRepo;
		$repo = Git::open($repoPath);
		notify($repo->status(true), 'Git Status before push');
		$repo->push('origin', 'master');
		notify($repo->status(true), 'Git Status after push');
	} catch(Exception $e){
		echo $e->getMessage();
	}
}

function copytoeqdkp($strRepo){
	global $repo_dir, $eqdkp_dir;
	
	$arrRepo = explode('-', $strRepo);
	$strPrefix = $arrRepo[0];
	$strFoldername = str_replace($strPrefix.'-', "", $strRepo);

	$from = $repo_dir.$strRepo.'/';
	
	switch($strPrefix){
		case 'game': $to = $eqdkp_dir.'games/'; break;
		
		case 'plugin': $to = $eqdkp_dir.'plugins/'; break;
		
		case 'module':
		case 'portalmodule':
		case 'portal': $to = $eqdkp_dir.'portal/'; break;
		
		case 'template':
		case 'style': $to = $eqdkp_dir.'templates/'; break;
		
		default: $to = $eqdkp_dir;
	}
	
	$to = $to.$strFoldername.'/';
	full_copy($from, $to);
	notify('Done. '.$strRepo, 'Copy to EQdkp');
}

function copytorepo($strRepo){
	global $repo_dir, $eqdkp_dir;

	$arrRepo = explode('-', $strRepo);
	$strPrefix = $arrRepo[0];
	$strFoldername = str_replace($strPrefix.'-', "", $strRepo);

	$from = $repo_dir.$strRepo.'/';

	switch($strPrefix){
		case 'game': $to = $eqdkp_dir.'games/'; break;
		
		case 'plugin': $to = $eqdkp_dir.'plugins/'; break;
		
		case 'module':
		case 'portalmodule':
		case 'portal': $to = $eqdkp_dir.'portal/'; break;
		
		case 'template':
		case 'style': $to = $eqdkp_dir.'templates/'; break;
		
		default: $to = $eqdkp_dir;
	}

	$to = $to.$strFoldername.'/';
	full_copy($to, $from);
	notify('Done. '.$strRepo, 'Copy to Repo');
}

function deletefromeqdkp($strRepo){
	global $repo_dir, $eqdkp_dir;

	$arrRepo = explode('-', $strRepo);
	$strPrefix = $arrRepo[0];
	$strFoldername = str_replace($strPrefix.'-', "", $strRepo);

	$from = $repo_dir.$strRepo.'/';

	switch($strPrefix){
		case 'game': $to = $eqdkp_dir.'games/'; break;
		
		case 'plugin': $to = $eqdkp_dir.'plugins/'; break;
		
		case 'module':
		case 'portalmodule':
		case 'portal': $to = $eqdkp_dir.'portal/'; break;
		
		case 'template':
		case 'style': $to = $eqdkp_dir.'templates/'; break;
		
		default: $to = $eqdkp_dir;
	}

	$to = $to.$strFoldername.'/';
	delete($to);
	notify('Done. '.$strRepo, 'Delete from EQdkp');
}

function check_if_repo_in_eqdkp(){
	global $repo_dir, $eqdkp_dir;
	$arrOut = array();
	$arrRepos = scanRepoDirectory();
	foreach($arrRepos as $strRepo){
		$arrRepo = explode('-', $strRepo);
		$strPrefix = $arrRepo[0];
		$strFoldername = str_replace($strPrefix.'-', "", $strRepo);
		
		$from = $repo_dir.$strRepo.'/';
		
		switch($strPrefix){
			case 'game': $to = $eqdkp_dir.'games/'; break;
		
			case 'plugin': $to = $eqdkp_dir.'plugins/'; break;
		
			case 'module':
			case 'portalmodule':
			case 'portal': $to = $eqdkp_dir.'portal/'; break;
		
			case 'template':
			case 'style': $to = $eqdkp_dir.'templates/'; break;
		
			default: $to = $eqdkp_dir;
		}
		
		$to = $to.$strFoldername.'/';
		if(is_dir($to)) $arrOut[$strRepo] = $strRepo;
	}
	return $arrOut;
}

//Init
bootstrap();
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" media="screen" href="includes/jquery-ui.css" />
		<script type="text/javascript" language="javascript" src="includes/jquery.min.js"></script>
		<script type="text/javascript" language="javascript" src="includes/jquery-ui.min.js"></script>
		<script type="text/javascript" language="javascript" src="includes/jquery-notification.js"></script>
		<link rel="stylesheet" type="text/css" media="screen" href="includes/repohelper.css" />
	<script>
		$(document).ready(function(){
			$("#container").notify({
				  speed: 500,
				  expires: false
			});
			<?php echo $docready; ?>
		});
	</script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Git Repository Helper</title>
</head>

<body>
		<form action="" method="post" id="form_install">
		<div id="header">
				<div id="logo"></div>
				<div id="logotext">Git Repository Helper</div>
		</div>
		<div id="installer">
			<div id="main">
				<div id="content">

					<h1 class="hicon home">Repository Helper</h1>
					
					<table style="border-collapse: collapse; width: 100%">
					<thead class="ui-state-default">
					<tr>
						<th width="20%" class="nowrap">Name</th>
						<th width="">Options</th>
					</tr>
					</thead>
					<tbody>
					<tr>
							<td class="nowrap">EQdkp Plus Core Local</td>
							<td>
								<form action="" method="post">
									<input type="hidden" name="repo" value="local_core" />
									<button type="submit" name="gitupdate">Git Update Core</button>
									<input type="text" name="gitcommitmsg" size="30" value="Update" />
									<button type="submit" name="gitcommit">Git Commit Core</button>
								</form>
							</td>
						</tr>
						<?php foreach($arrRepos as $key => $elem) {?>
						<tr>
							<td class="nowrap"><?php echo $key; ?></td>
							<td>
								<form action="" method="post">
									<input type="hidden" name="repo" value="<?php echo $key; ?>" />
									<button type="submit" name="copytoeqdkp">Copy to EQdkp</button>
									<button type="submit" name="copytorepo" <?php if(!isset($arrReposAtEQdkp[$key])) echo "disabled='disabled'"; ?>>Copy to Repo</button>
									<button type="submit" name="deletefromeqdkp" <?php if(!isset($arrReposAtEQdkp[$key])) echo "disabled='disabled'"; ?>>Delete from EQdkp</button><br />
									<button type="submit" name="gitupdate">Git Update</button>
									<input type="text" name="gitcommitmsg" size="30" value="Update" />
									<button type="submit" name="gitcommit">Git Commit</button>
								</form>
							</td>
						</tr>
						<?php }?>
					</tbody>
					</table>

					<div class="buttonbar">
					</div>
				</div>
			</div>
		</div>
		<div id="footer">
			EQDKP Plus Repo Helper © 2014 - <?php echo date('Y', time()); ?> by GodMod
		</div>
		</form>
		
		
		<div id="container" style="display:none">
		  <!-- 
		  Later on, you can choose which template to use by referring to the 
		  ID assigned to each template.  Alternatively, you could refer
		  to each template by index, so in this example, "basic-tempate" is
		  index 0 and "advanced-template" is index 1.
		  -->
		  <div id="basic-template">
		      <a class="ui-notify-cross ui-notify-close" href="#">x</a>
		      <h1>#{title}</h1>
		      <p>#{text}</p>
		  </div>
		
		  <div id="advanced-template">
		      <!-- ... you get the idea ... -->
		  </div>
		</div>
	</body>
</html>