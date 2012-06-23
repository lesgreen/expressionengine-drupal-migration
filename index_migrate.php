<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * The routines here dispatch control to the appropriate handler, which then
 * prints the appropriate page.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 */

/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());
    /**
    * Copyright (c) 2006 Tarek Lubani <tarek@tarek.2y.net>
    * This code is hereby licensed for public consumption under the
    * GNU GPL v2.
    * You should have received a copy of the GNU General Public License
    * along with this program; if not, write to the Free Software
    * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
    */
    /* $Id$ */
    /**
    * ExpressionEngine 1.0 -> Drupal Migration
    * See ee_migrate.php for more information
        */
	$version = "2.0";

	// First, configuration options

    // ExpressionEngine
    $ee['user'] = 'youruser'; // ExpressionEngine database user
    $ee['pass'] = 'yourpassword'; // ExpressionEngine database password
    $ee['host'] = 'localhost'; // ExpressionEngine database host
    $ee['dbname'] = 'mriswebexpeng'; // ExpressionEngine database name
    $ee['prefix'] = 'exp_'; // ExpressionEngine database table prefix
    $pub_rec_fed = array();
	$pub_rec_state = array();

	// Start the engine!

    // Include Drupal bootstrap
    include_once "includes/bootstrap.inc";
    include "includes/common.inc";

    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

    // If not in 'safe mode', increase the maximum execution time:
    if (!ini_get('safe_mode')) {
     set_time_limit(240);
    }

	$step1 = "Step 1: Import Users";
	$step2 = "Step 2: Import Categories";
	$step3 = "Step 3: Import Posts";
	
	// First page
	if ($_GET['step'] == 1) {
	
	echo "
	<html>
	<head>
	  <title>$step1</title>
	</head>
	<body>
	<h2>$step1</h2>";
	ConvertUsers($ee);
	echo "<p>Next: <b><a href=\"?step=2\">$step2</a></b></p>
	</body>
	</html>";
	exit;
	
	}
	
	elseif ($_GET['step'] == 2) {
	echo "
	<html>
	<head>
	  <title>$step2</title>
	</head>
	<body>
	<h2>$step2</h2>";
	ConvertCategories($ee);
	echo "<p>Next: <b><a href=\"?step=3\">$step3</a></b></p>
	</body>
	</html>";
	exit;
	
	}
	
	elseif ($_GET['step'] == 3) {
	echo "
	<html>
	<head>
	  <title>$step3</title>
	</head>
	<body>
	<h2>$step3</h2>";
	ConvertPosts($ee);
	echo "<p>Done! Hopefully it all worked!</p>
	</body>
	</html>";
	exit;
	
	}
	else {
	echo "
	<html>
	<head>
	  <title>EE -> Drupal conversion $version</title>
	</head>
	<body>
	<p>This script will convert EE to Drupal. It has only been tested on EE version 1.68 and Drupal 7</p>
	<ul>
	<li><a href=\"?step=1\">$step1</a></li>
	<li><a href=\"?step=2\">$step2</a></li>
	<li><a href=\"?step=3\">$step3</a></li>
	</ul>";
	exit;
	}

//
// The purpose of this function is to convert users from EE and convert them to drupal
//
function ConvertUsers($ee) {
    // Load all users from EE
    //$sql = "SELECT username,password,email,url FROM ".$ee[prefix]."members WHERE group_id=1 OR group_id=6";
    $sql = "SELECT username,password,email,url FROM ".$ee['prefix']."members order by member_id";
    $eeUsers = mysqlQuery($ee,$sql);
    foreach($eeUsers as $eeUser) {
    //$drupalUser = user_load(array('name' => $eeUser['username']));
    $drupalUser = user_load_by_name($eeUser['username']);
        if (!$drupalUser) {
            $user_array = array("name" => $eeUser['username'], "pass" => $eeUser['password'], "mail" => $eeUser['email'], "status" => 1);
            user_save($account, $user_array);
        echo "Added <i>$eeUser[username] <br />";
        }
    else{
     echo "$eeUser[username] already exists <br />";
        }
    }
}

//
// The purpose of this function is to convert categories from EE to drupal
//
function ConvertCategories($ee) {
	$sql = "SELECT cat_id,parent_id,cat_name from ".$ee['prefix']."categories";
	$eeCategories = mysqlQuery($ee,$sql);
	foreach ($eeCategories as $eeCategory) {
	  $taxonomy = taxonomy_get_term_by_name($eeCategory['cat_name']);
	  if (!$taxonomy) {
	  		$new_term = array(
			    'vid' => 1,
			    'name' => $eeCategory['cat_name'],
			);
			$new_term = (object) $new_term;
			taxonomy_term_save($new_term);
	   		unset($term);
	   		echo 'Imported category <strong>'. $eeCategory['cat_name'] . '</strong>'.'<br />';
	  }
	  else {
	   echo "Category <b>$eeCategory[cat_name]</b> already exists as ".$taxonomy[0]->tid."<br />";
	  }
	}
}

//
// The purpose of this function is to convert posts from EE and convert them to drupal
//

function ConvertPosts($ee) {
	$menuItems = array();     
    $weblog_field_map['3'] =  array('teaser' => 'field_id_19', 'body' => 'field_id_20'); //careers
	$weblog_field_map['5'] =  array('teaser' => '', 'body' => 'field_id_203', 'start_time' => 'field_id_198', 'end_time' => 'field_id_199', 'start_date' => 'field_id_200', 'end_date' => 'field_id_201', 'location' => 'field_id_202'); //events
	$weblog_field_map['6'] =  array('teaser' => 'field_id_9', 'body' => 'field_id_10'); //news
	$weblog_field_map['8'] =  array('teaser' => '', 'body' => 'field_id_6'); //page
	$weblog_field_map['29'] = array('teaser' => '', 'body' => 'field_id_154', 'company_title' => 'field_id_159', 'image_name' => 'field_id_160', 'sort_order' => 'field_id_163'); //leadership
	$weblog_field_map['37'] = array('teaser' => 'field_id_9', 'body' => 'field_id_10'); //whitepapers
	$weblog_field_map['44'] = array('teaser' => 'field_id_14', 'body' => 'field_id_190', 'buy_now_url' => 'field_id_226'); //products 
	$weblog_field_map['47'] = array('teaser' => '', 'body' => 'field_id_227'); //faqs  SELECT * FROM `exp_matrix_data` WHERE entry_id =264
	$weblog_field_map['49'] = array('county_id' => 'field_id_230', 'official_site_link' => 'field_id_231', 'school_information_link' => 'field_id_232', 'school_boundary_link' => 'field_id_233', 'school_search_link' => 'field_id_234', 'county_home_page_link' => 'field_id_235', 'county_munic_link' => 'field_id_236', 'known_issues' => 'field_id_237', 'local_realtor_assoc_link_1' => 'field_id_238', 'local_realtor_assoc_link_2' => 'field_id_239', 'metro_council_link' => 'field_id_243', 'local_tax_incent_program_link_1' => 'field_id_240', 'local_tax_incent_program_info_1' => 'field_id_250', 'local_tax_incent_program_link_2' => 'field_id_241', 'local_tax_incent_program_info_2' => 'field_id_251', 'local_tax_incent_program_link_3' => 'field_id_242', 'local_tax_incent_program_info_3' => 'field_id_252', 'additional_link_1' => 'field_id_244', 'additional_info_1' => 'field_id_246', 'additional_link_2' => 'field_id_245', 'additional_info_2' => 'field_id_247', 'additional_link_3' => 'field_id_248', 'additional_info_3' => 'field_id_249', 'additional_link_4' => 'field_id_287', 'additional_info_4' => 'field_id_289', 'additional_link_5' => 'field_id_288', 'additional_info_5' => 'field_id_290');//public_records_county
	$weblog_field_map['50'] = array('dept_hud_link' => 'field_id_253', 'national_register_historic_link' => 'field_id_254', 'heritage_pres_services_link' => 'field_id_285', 'national_trust_historic_pres_link' => 'field_id_255', 'fema_link' => 'field_id_256', 'nars_link' => 'field_id_257', 'additional_link_1' => 'field_id_258', 'additional_info_1' => 'field_id_261', 'additional_link_2' => 'field_id_259', 'additional_info_2' => 'field_id_262', 'additional_link_3' => 'field_id_260', 'additional_info_3' => 'field_id_263'); //public_records_federal
	$weblog_field_map['51'] = array('public_records_site_link' => 'field_id_272', 'state_home_page_link' => 'field_id_273', 'dept_assess_tax_link' => 'field_id_264', 'bus_econ_dev_link' => 'field_id_267', 'elections_info_link' => 'field_id_265', 'data_center_link' => 'field_id_266', 'state_known_info' => 'field_id_277', 'electronic_res_link_1' => 'field_id_274', 'state_realtor_assoc_link' => 'field_id_270', 'state_licensing_info_link' => 'field_id_271', 'state_tax_incent_program_link_1' => 'field_id_268', 'state_tax_incent_program_info_1' => 'field_id_257', 'state_tax_incent_program_link_2' => 'field_id_269', 'state_tax_incent_program_info_2' => 'field_id_276', 'additional_link_1' => 'field_id_278', 'additional_info_1' => 'field_id_281', 'additional_link_2' => 'field_id_279', 'additional_info_2' => 'field_id_282', 'additional_link_3' => 'field_id_280', 'additional_info_3' => 'field_id_283'); //public_records_state
	$weblog_field_map['53'] = array('teaser' => 'field_id_9', 'body' => 'field_id_10'); //association_storybook
	
    // two iterations. 1st iteration - queries most of the content types. 2nd iteration - loads public records. loading public records separately because, we have to load federal and state records before county, but the content type for county comes first
    $msql = 'SELECT entry_id, weblog_id, title, author_id, url_title, entry_date, edit_date FROM '.$ee['prefix'].'weblog_titles WHERE status = "open"';
    for ($i=0; $i<2; $i++) {
	    $sql = $msql;
	    if ($i == 0) {
			// some of the content types contain test data, so I do not want to migrate the content (12, 24, 26, 30)
			// I also don't want 49, 50, and 51 because the public records query will be run on the 2nd iteration.
		    $sql .= ' and (weblog_id NOT IN ( 12, 24, 26, 30, 49, 50, 51 )) order by entry_id';
		} else {
			$sql .= ' and (weblog_id IN ( 49, 50, 51 )) ORDER BY weblog_id desc, entry_id';
		}
		echo $sql . '<br />';	
		$titles = mysqlQuery($ee,$sql);
		foreach ($titles as $title) {
			$type = getWeblogInfo($ee, $title['weblog_id']);
			echo '<strong>Blog Name:</strong> ' .$type['blog_name'] . '<br />';
			$contentType = getContentType($type['blog_name']);
			//$contentType = ($contentType == 'products') ? 'mris_products' : $contentType;
			$lang = $type['blog_lang'];
			// get fields for content type
			echo 'The Group: '. $type['field_group'] . '<br />';
			$fields = getEEFields($ee, $type['field_group']);
			
			// get path;
			echo 'getting the path '. $title['entry_id'] . '<br />';
			$path = getURLPath($ee, $title['entry_id'], $title['url_title'], $type['blog_name']);
			
			// Load associated username
		    $sql = 'SELECT username FROM '.$ee['prefix'].'members WHERE member_id='.$title['author_id'];
		    $eeUser = mysqlQuery($ee,$sql, TRUE);
			$drUser = user_load_by_name($eeUser['username']);
			
			// Get fields - returns '3' or '49', etc
			$weblog_id = sprintf('%s', $title['weblog_id']);
			
     		$sql = 'SELECT '. $fields . ' FROM '.$ee['prefix'].'weblog_data WHERE entry_id='.$title['entry_id'];
			echo $sql . '<br />';
			$ee_content = mysqlQuery($ee,$sql, TRUE);
			
			$teaser = '';
			if (!in_array($type['weblog_id'], array("49", "50", "51"))) {
				if ($weblog_field_map[$weblog_id]['teaser'] != '') {
			    	$teaser = $ee_content[$weblog_field_map[$weblog_id]['teaser']];
				}
			}
			
			$node = new stdClass();
			$node->type = $contentType; 
			node_object_prepare($node);
			
			$node->title    = $title['title'];
			$node->language = 'und';
			$node->uid = $drUser->uid;
			if (!in_array($type['weblog_id'], array("49", "50", "51"))) {
				$body = updateImagePath($contentType, $ee_content[$weblog_field_map[$weblog_id]['body']]);
				//echo $body . '<br /><br />';
				$node->body['und'][0]['value']  = $body;
			}
			if ($teaser != '') {
				$node->body['und'][0]['summary'] = $teaser;
			}
			$node->body['und'][0]['format']  = 'full_html';
			echo 'next=======================================<br />';	
			// make sure pathauto is not enabled -  will cause duplicate entries in url_alias		
			$node->path = array('alias' => $path['path']);
			$node->status = 1;
		    $node->promote = 0;
		    $node->sticky = 0;
		    $node->comment = 0;
			$node->created = $title['entry_date'];
		    $node->changed = $title['edit_date'];
			
			if ($title['weblog_id'] == 5) {
				$node->field_event_location['und'][0]['value'] = $ee_content[$weblog_field_map['5']['location']];
				$node->field_event_date['und'][0]['value'] = date('Y-m-d H:i:s', $ee_content[$weblog_field_map['5']['start_date']]);
				$node->field_event_date['und'][0]['value2'] = date('Y-m-d H:i:s', $ee_content[$weblog_field_map['5']['end_date']]);
			} 
			if ($title['weblog_id'] == 29) {				
				$file = save_mris_leadership_file($ee_content[$weblog_field_map['29']['image_name']], $drUser->uid); 
				//$node->field_image = array(LANGUAGE_NONE => array('0' => (array)$file));
				//$node->field_image['und'][0] = (array)$file;
			}
			if($node = node_submit($node)) { // Prepare node for saving
			    node_save($node);
			    echo "Node with nid " . $node->nid . " saved!<br />";
				$vid = $node->vid;
				
				$proceed = loadRoles($ee, $title['entry_id'], $node->nid);
				
				$m = sprintf('%s', $vid);
				$menuItems[$m] = array('path' => $path['path'], 'parentUrl' => $path['parentUrl'], 'title' => $title['title'], 'urlTitle' => $title['url_title'], 'blogName' => $type['blog_name']);
				
				//http://fooninja.net/2011/04/13/guide-to-programmatic-node-creation-in-drupal-7/
				if ($title['weblog_id'] == 29) {
					if ($file) {
						file_usage_add($file, 'mris_leadership', 'node', $node->nid);
						$proceed = loadLeadershipDetails($ee, $title['entry_id'], $vid, $ee_content[$weblog_field_map['29']['company_title']], $file->fid);
					}
				} else if ($title['weblog_id'] == 44) {
					$proceed = loadProductDetails($ee, $title['entry_id'], $vid, $ee_content[$weblog_field_map['44']['buy_now_url']]);
				} else if ($title['weblog_id'] == 47) {
					$proceed = loadFAQDetails($ee, $title['entry_id'], $vid);
				} else if ($title['weblog_id'] == 49) {
					$proceed = loadPublicRecordsCounty($ee, $title['entry_id'], $title['url_title'], $vid, $ee_content, $weblog_field_map['49']);
				} else if ($title['weblog_id'] == 50) {
					$proceed = loadPublicRecordsFederal($title['entry_id'], $vid, $ee_content, $weblog_field_map['50']);
				} else if ($title['weblog_id'] == 51) {
					$proceed = loadPublicRecordsState($title['entry_id'], $vid, $ee_content, $weblog_field_map['51']);
				}	 
			}				
			unset($node, $file);	
			echo 'WeblogId: ' . $title['weblog_id']. ' EnttryId: ' . $title['entry_id'] . ' Title: '. $title['title'] . ' completed <br />';
		} //end foreach
	} // end for
	$proceed = createMainMenu($menuItems);
	$proceed = createNavigationMenus();
	//$proceed = migrateImages();
	$file = '';
}

function mysqlQuery($db, $query_record, $single = FALSE) {
	$mysql_database = $db['dbname'];
	$mysql_hostname = $db['host'];
	$mysql_username = $db['user'];
	$mysql_password = $db['pass'];
	
	$mysql_all = mysql_pconnect($mysql_hostname, $mysql_username, $mysql_password) or die(mysql_error());
	
	mysql_select_db($mysql_database, $mysql_all);
	$result = mysql_query($query_record, $mysql_all) or die(mysql_error());
	if (!$single) {
		while ($a = mysql_fetch_array($result)) { $resultSet[] = $a; }
	} else {
		$resultSet = mysql_fetch_assoc($result);
	}	
	return $resultSet;
}

function getWeblogInfo($ee, $weblogId) {
	$sql = 'SELECT blog_name, blog_lang, field_group FROM ' .$ee['prefix'].'weblogs where weblog_id = '. $weblogId; 
	return mysqlQuery($ee,$sql, TRUE); 
}

function getContentType($blogName) {
	if ($blogName == 'faqs') {
		return 'faq_pages';
	} else if ($blogName == 'products') {
		return 'mris_products';
	} else if ($blogName == 'leadership') {
		return 'mris_leadership';	
	} else {
		return $blogName;
	}
}

function getEEFields($ee, $group) {
	$fields = '';
	$prefix = 'field_id_';
	$sql = 'SELECT field_id, field_name, field_order FROM '.$ee['prefix'].'weblog_fields WHERE group_id = ' . $group;
	//echo 'Fields Query: '. $sql . '<br />';
	//echo 'Group again: '. $group . '<br />';
	$rows = mysqlQuery($ee,$sql);
	foreach($rows as $row) {
		//echo 'field name: ' . $row['field_name'];
		$pos = strpos($row['field_name'], 'meta');
		// I don't want meta fields
		if ($pos == false) {
			// I don't want Content Components List fields
			if ($row['field_order'] != '999') {
				$fields .= $prefix . $row['field_id'] . ',';
			}	
		}
	}
	//echo 'fields: '. $fields . '<br />';
	// remove last comma
	if ($fields != '') {
		$fields = substr($fields, 0, -1);
	}
	
	return $fields;	
}

// get website struture from exp_structure
function getURLPath($ee, $entryId, $urlTitle, $blogName) {
	$path = $urlTitle;
	$parentUrl = '';
	$sql = 'SELECT parent_id FROM '.$ee['prefix'].'structure WHERE entry_id = ' . $entryId;
	$row = mysqlQuery($ee,$sql, TRUE);
	//print_r($row['parent_id']);
	echo 'the row count: '. count($row). ' <br />';
	if (!isset($row['parent_id'])) {
		echo 'my count is 0, blogName: ' . $blogName. '<br />';
		if (in_array($blogName, array('public_records_state', 'public_records_county', 'public_records_federal'))) {
			return array('path' => $blogName . '/'. $urlTitle, 'parentUrl' => $blogName);
		}
		if ($blogName == 'association_storybank') {
			return array('path' => 'about-mris/mris-media-kit/association-storybank/'. $urlTitle, 'parentUrl' => $blogName);
		}
		if ($blogName == 'whitepapers') {
			$sql = "select entry_id, url_title from ".$ee['prefix']."weblog_titles where title = 'Original Research'";
		} else {
			$sql = "select entry_id, url_title from ".$ee['prefix']."weblog_titles where title = '$blogName'";//'Careers'
		}
		echo $sql . '<br />';
		$row = mysqlQuery($ee,$sql, TRUE);
		$rowTitle = $row['url_title'];
		// url_title is incorrect for some content types
		// must have been changed by
		if ($rowTitle == 'for-associations') {
			$rowTitle = 'mris-customers';
		} else if ($rowTitle == 'for-consumers') {
			$rowTitle = 'consumers';
		} else if ($rowTitle == 'for-real-estate-professionals') {
			$rowTitle = 'mris-products';
		} else if ($rowTitle == 'for-tech-partners') {
			$rowTitle = 'tech-partners';
		} else if ($rowTitle == 'compliance1') {
			$rowTitle = 'compliance';
		}
		$path = $row['url_title'] . '/' . $path;
		$parentUrl = $row['url_title'];
		$sql = 'select parent_id from '.$ee['prefix'].'structure where entry_id = '. $row['entry_id']; //38
		echo $sql  . '<br />';
		$row = mysqlQuery($ee,$sql, TRUE);
	}
	
	if ($row['parent_id'] != 0) { 
		$finished = false;                       
		while ( ! $finished ):                   
		  	$path_array = getParentPath($ee, $row['parent_id']);
			$path = $path_array['path'] . '/' . $path;
			$parentUrl = $path_array['path'] . '/' . $parentUrl;
			if (($path_array['parent_id'] == 0) || ($path_array['parent_id'] == '')) {
				$finished = true;
			} else {
				$row['parent_id'] = $path_array['parent_id'];
			}	
		endwhile;
	}		
	echo '<strong>************* path: '. $path . ' ****** parentUrl: ' . $parentUrl . ' ***** entryId: ' . $entryId . '</strong><br />'; 
	
	return array('path' => $path, 'parentUrl' => $parentUrl);
}

function getParentPath($ee, $parentId) {
	$sql = 'select entry_id, url_title from '.$ee['prefix'].'weblog_titles where entry_id = '. $parentId;
	$row = mysqlQuery($ee,$sql, TRUE);
	$path = $row['url_title'];
	$sql = 'select parent_id from '.$ee['prefix'].'structure where entry_id = '. $row['entry_id']; //38
	$row = mysqlQuery($ee,$sql, TRUE);
	return array('path' => $path, 'parent_id' => $row['parent_id']);
}

function updateImagePath($contentType, $body) {
	$file_path = 'sites/default/files/';
	$images = array();
	$new_body = $body;
	preg_match_all( '/src="(.*?)"/i', $body, $images ) ;
	$images = $images[1];	
	if (count($images) > 0) {
		for ($i = 0; $i<count($images); $i++) {
			//echo  $images[$i] . '<br />';
			if (substr($images[$i], 0, 4) != 'http') {				
				$parts = explode('/', $images[$i]);
				if (count($parts) > 0) {
					if (count($parts) == 1) {
						//echo $parts[0] . '<br />';
						//{filedir_1}myimage.jpg
						$noslash = explode('}', $parts[0]);
						$image = $noslash[1];
					} else {
						$image = $parts[count($parts) - 1];
					}
					$image_parts = explode('.', $image);
					if (count($image_parts) > 0) {
						$image_type = $image_parts[1];
						//echo 'image type: ' . $image_type . '<br />';
						if (in_array($image_type, array('jpg', 'JPG', 'jpeg', 'png', 'gif'))) {
							$ee_path_prefix = explode('}', $parts[0]);
							$original_path = getOriginalImagePath($ee_path_prefix[0]) . $ee_path_prefix[1]; 
							for ($j=1; $j<count($parts); $j++) {
								$original_path .= '/' . $parts[$j];
							}
							echo $original_path . '<br />';
							$new_path = $file_path . $contentType . '/' . $image;
							echo $new_path . '<br /><br />';
							$new_body = str_replace($images[$i], $new_path, $body);
							if (!copy($original_path, $new_path)) {
							    echo "failed to copy $original_path...\n";
							} else {
								echo 'copy ' . $new_path . ' was successful <br /><br />';
							}
							
						}
					} // if count
				} // if count
			} // if not http
		} // end for $i
	}
	return $new_body;
}

function getOriginalImagePath($tag) {
	$paths['{filedir_1'] = '_res/img/';
	// bad data
	$paths['..{filedir_1'] = '_res/img/';
	$paths['{filedir_10'] = '_res/_thumbs/Images/';
	$paths['_7'] = '_res/img/photos/';
	return $paths[$tag];
}

function createMainMenu($menuItems) {
	$proceed = createFooterMenu();
	$footerItems = array('Site Map', 'Feedback', 'Privacy Policy', 'Terms of Use', 'Copyright Notice', 'Disclaimer', 'Fair Housing');
	foreach ($menuItems as $key => $value) {
		$link = array();
		if (in_array($value['title'], $footerItems)) {
			$link['menu_name'] = 'menu-footer-menu';
		} else {
			$link['menu_name'] = 'main-menu';
		}
		$parentUrl = rtrim($value['parentUrl'], '/');
		echo '<strong>title: '. $value['title'] . ' parentUrl: ' .$parentUrl . ' blogName: '. $value['blogName']. '</strong><br />';
		// Public records has no menu link. 
		if (!in_array($parentUrl, array('public_records_county', 'public_records_state', 'public_records_federal'))) {
			$levels = explode('/', $parentUrl);			
			//I only want two levels for the main menu
			if (count($levels) == 1) {
				// create an array to store menu items
				// see menu_link_save http://api.drupal.org/api/drupal/includes!menu.inc/function/menu_link_save/7
				$link['link_title'] = $value['title']; 
				$link['link_path'] = 'node/'. $key; 
				//$link['router_path'] = $value['path'];
				if ($value['parentUrl'] != '') {
					$url_alias = db_query('SELECT source FROM {url_alias} WHERE alias = :pUrl', array(':pUrl' => $parentUrl))->fetchAssoc();
					echo 'alias source: ' . $url_alias['source'] . '<br />';
					$parent = db_query('SELECT mlid FROM {menu_links} WHERE link_path = :lnkPath', array(':lnkPath' => $url_alias['source']))->fetchAssoc();
					$link['plid'] = $parent['mlid'];
				}
				menu_link_save($link);
				unset($link);
			} 
		}
	}
	menu_cache_clear_all();	
	return TRUE;
}

function createFooterMenu() {
	$menu = array();
	$menu['menu_name'] = 'menu-footer-menu';
	$menu['title'] = 'Footer Menu';
	$menu['description'] = 'Footer Menu Links';
	menu_save($menu);
	return TRUE;
}

function createNavigationMenus() {
	$result = db_query("SELECT mlid, link_path, link_title FROM {menu_links} WHERE menu_name = 'main-menu' and plid = 0 and has_children = 1")->fetchAll();
	foreach ($result as $main_menu) {
		$menu = array();
		$parent_link = array();
		$menuName = 'menu-' . strtolower(str_replace(' ', '-', $main_menu->link_title));
		$menu['menu_name'] = $menuName;
		$menu['title'] = $main_menu->link_title;
		$menu['description'] = $main_menu->link_title . ' Menu Links';
		menu_save($menu);
		
		$parent_link['menu_name'] = $menuName;
		$parent_link['link_title'] = $main_menu->link_title; 
		$parent_link['link_path'] = $main_menu->link_path; 
		menu_link_save($parent_link);
		unset($parent_link);
		// select all of the children of the main menu item
		$children = db_query('SELECT mlid, link_path, link_title, has_children FROM {menu_links} WHERE plid = :mlid', array(':mlid' => $main_menu->mlid))->fetchAll();
		foreach ($children as $child) {
			$link = array();
			$link['menu_name'] = $menuName;
			$link['link_title'] = $child->link_title; 
			$link['link_path'] = $child->link_path; 
			menu_link_save($link);
			
			// select the child from url_alias using the link_path (node/123)
			$url_alias = db_query('SELECT source, alias FROM {url_alias} WHERE source = :src', array(':src' => $child->link_path))->fetchAssoc();
			$alias = $url_alias['alias'];//	about-mris/our-culture
			// select all children that have the same url prefix - about-mris/current-associations%
			$result = db_query('SELECT alias, source FROM {url_alias} WHERE alias like :als order by alias', array(':als' => db_like($alias) . '%'));
			if ($result->rowCount() > 1) {
				$rows = $result->fetchAll();
				foreach ($rows as $row) {
					// I have already created a link with the current alias, so I don't want to load a duplicate 
					if ($row->alias != $alias) {
						// split the path
						$levels = explode('/', $row->alias);
						echo '<strong>********************** Level: ' .$levels[1] . '</strong><br />';
						// I don't want the details pages for leadership, news, events, and careers to be displayed in the navigation menu
						if (!in_array($levels[1], array('leadership', 'news', 'events', 'careers', 'original-research'))) {
							$child_link = array();	
							$vid = explode("/", $row->source);
							// get the title from the node table
							$title = db_query('SELECT title FROM {node} WHERE vid = :src', array(':src' => $vid[1]))->fetchAssoc();
							
							$child_link['menu_name'] = $menuName;
							$child_link['link_title'] = $title['title'];
							$child_link['link_path'] = $row->source;
							echo 'title: ' . $title['title'] . '<br />';
							
							$cnt = count($levels);
							echo 'the count: ' . $cnt . '<br />';
							// I only want the first child - the third level. 
							// main_menu/this parent/child 1
							if ($cnt == 3) {
								$mlid = db_query('SELECT mlid FROM {menu_links} WHERE link_path = :lp and menu_name = :mn', array(':lp' => $child->link_path, ':mn' => $menuName))->fetchAssoc();
								$child_link['plid'] = $mlid['mlid'];
								echo 'mlid: ' . $mlid['mlid'] . '<br />';
								
							} else if ($cnt > 3) {
								
								$parentUrl = '';
								// count - 2 will give us the parent
								// so if the full url is: 
								// mris-products/core-products-services/keystone/media-connect
								// the parent will be:
								// mris-products/core-products-services/keystone
								for ($i=0; $i<$cnt-2; $i++) {
									$parentUrl .= $levels[$i] . '/';
								}
								echo 'parent: '. $parentUrl . '<br />';
								if ($parentUrl != '') {
									$parentUrl = rtrim($parentUrl, '/'); 
									$source = db_query('SELECT source FROM {url_alias} WHERE alias = :als', array(':als' => $parentUrl))->fetchAssoc();
									$mlid = db_query('SELECT mlid FROM {menu_links} WHERE link_path = :lp and menu_name = :mn', array(':lp' => $source['source'], ':mn' => $menuName))->fetchAssoc();
									$child_link['plid'] = $mlid['mlid'];
								}
							}
							menu_link_save($child_link);
							unset($child_link);
						} // end if not leadership, careers, news
					} // end if child->alias != alias 
				}
			}	
			unset($link);
		}
		unset($menu);
	}
	return TRUE;
}

function getTaxTermId($ee, $entryId) {
	$name = getCategoryName($ee, $entryId);
	if($tids = taxonomy_get_term_by_name($name)) {
	    $tids_keys = array_keys($tids);
	    return $tids_keys[0];
	} else {
		return FALSE;
	}
		
	/*if ($name == '') {
		return FALSE;
	} else {
		$query = new EntityFieldQuery;
		$result = $query
	  	->entityCondition('entity_type', 'taxonomy_term')
	  	->propertyCondition('name', $name)
	  	->propertyCondition('vid', 1)
	  	->execute();
		
		 $tids = array_keys($result['taxonomy_term']);
  	  	return $tids[0];
	} */ 
  
  /*  
    Array
	(
	    [taxonomy_term] => Array
	        (
	            [5] => stdClass Object
	                (
	                    [tid] => 5
	                )
	        )
	)
   */
}

function save_mris_leadership_file($imageName, $uid) {
	$img = '_res/img/photos/' . $imageName;
	$image = image_load($img);
	if ($image) {
		image_scale($image, 112, 160);
		image_save($image, 'sites/default/files/mris_leadership/'. $imageName);
		
		$file = new stdClass;
		$file->uid = $uid;
		$file->filename = $imageName;
		$file->uri = 'public://mris_leadership/' . $imageName;
		$file->status = FILE_STATUS_PERMANENT;
		$file->filemime = mime_content_type($imageName);
		
		file_save($file);
		
		return $file;
	} else {
		return NULL;
	}
}

function loadLeadershipDetails($ee, $entryId, $vid, $companyTitle, $fid) {
	$cat_id = getTaxTermId($ee, $entryId);
	echo 'Category: '. $cat_id . '<br />';
	$nid = db_insert('mris_leadership')
	  ->fields(array(
	    'vid' => $vid,
	    'company_title' => $companyTitle,
	    'category_id' => $cat_id,
	    'image_fid' => $fid,
	  ))
	  ->execute();  
	  
	return TRUE;  
}

function loadFAQDetails($ee, $entryId, $vid) {	
	$sql = 'SELECT col_id_6 as question, col_id_7 as answer FROM '.$ee['prefix'].'matrix_data WHERE entry_id='.$entryId;
	$faqs = mysqlQuery($ee,$sql);
	foreach ($faqs as $faq) {
		$nid = db_insert('faq_pages')
		  ->fields(array(
		    'vid' => $vid,
		    'question' => $faq['question'],
		    'answer' => $faq['answer'],
		  ))
		  ->execute();		
	}	
	return TRUE;
}

function loadRoles($ee, $entryId, $nid) {
	$sql = 'SELECT entry_id, role_id FROM '.$ee['prefix'].'mris_page_access WHERE entry_id='.$entryId;
	$roles = mysqlQuery($ee,$sql);
	foreach ($roles as $role) {
		$nid = db_insert('mris_auth_node_access')
		  ->fields(array(
		    'nid' => $nid,
		    'role_id' => $role['role_id'],
		  ))
		  ->execute();		
	}	
	return TRUE;
}

function loadProductDetails($ee, $entryId, $vid, $buynow) {
	$pid = db_insert('mris_products')
		  ->fields(array(
		    'vid' => $vid,
		    'buy_now_url' => $buynow,
		  ))
		  ->execute();
	
	$sql = 'SELECT col_id_8 as link_title, col_id_9 as link_url, col_id_10 as tab_title, col_id_11 as tab_content FROM '.$ee['prefix'].'matrix_data WHERE entry_id='.$entryId;
	echo '<strong>Product matrix query</strong> ' . $sql . '<br />';
	$tabs = mysqlQuery($ee,$sql);
	foreach ($tabs as $tab) {
		if (($tab['tab_title'] != NULL) and ($tab['tab_content'] != NULL)) { 
			$pid = db_insert('mris_products_tab_data')
			  ->fields(array(
			    'vid' => $vid,
			    'tab_title' => $tab['tab_title'],
			    'tab_content' => $tab['tab_content'],
			  ))
			  ->execute();
		}  
		if (($tab['link_title'] != NULL) and ($tab['link_url'] != NULL)) {
			$pid = db_insert('mris_products_links')
			  ->fields(array(
			    'vid' => $vid,
			    'link_title' => $tab['link_title'],
			    'link_url' => $tab['link_url'],
			  ))
			  ->execute();
		} 
	}	
	return TRUE;
}

function loadPublicRecordsCounty($ee, $entryId, $urlTitle, $vid, $ee_content, $fields_map) {
	global $pub_rec_fed, $pub_rec_state;
	// get state relationship - exclude federal	
	$sql = 'SELECT rel_child_id FROM '.$ee['prefix'].'relationships WHERE rel_parent_id='.$entryId . ' and rel_child_id != '.$pub_rec_fed['entryId'];
	echo '<strong>Public Records query: </strong>' . $sql . '<br />';
	
	print_r($pub_rec_state);
	echo $pub_rec_fed . '<br />';
	$rel = mysqlQuery($ee,$sql, TRUE);
	echo '<br />rel id: '. $rel['rel_child_id'] . '<br />';
	$state_id = 0;
	if ($rel['rel_child_id'] != '') {
		$assoc = sprintf('%s', $rel['rel_child_id']);
		$state_id = $pub_rec_state[$assoc];	
	}
	
	$nid = db_insert('public_records_county')
		  ->fields(array(
		    'vid' => $vid,
		    'federal_id' => $pub_rec_fed['vId'],
		    'state_id' => $state_id,
		    'url_title' => $urlTitle,
		    'county_id' => $ee_content[$fields_map['county_id']],
		    'official_site_link' => $ee_content[$fields_map['official_site_link']],
		    'school_information_link' => $ee_content[$fields_map['school_information_link']],
		    'school_boundary_link' => $ee_content[$fields_map['school_boundary_link']],
		    'school_search_link' => $ee_content[$fields_map['school_search_link']],
		    'county_home_page_link' => $ee_content[$fields_map['county_home_page_link']],
		    'county_munic_link' => $ee_content[$fields_map['county_munic_link']],
		    'known_issues' => $ee_content[$fields_map['known_issues']],
		    'local_realtor_assoc_link_1' => $ee_content[$fields_map['local_realtor_assoc_link_1']],
		    'local_realtor_assoc_link_2' => $ee_content[$fields_map['local_realtor_assoc_link_2']],
		    'metro_council_link' => $ee_content[$fields_map['metro_council_link']],
		    'local_tax_incent_program_link_1' => $ee_content[$fields_map['local_tax_incent_program_link_1']],
		    'local_tax_incent_program_info_1' => $ee_content[$fields_map['local_tax_incent_program_info_1']],
		    'local_tax_incent_program_link_2' => $ee_content[$fields_map['local_tax_incent_program_link_2']],
		    'local_tax_incent_program_info_2' => $ee_content[$fields_map['local_tax_incent_program_info_2']],
		    'local_tax_incent_program_link_3' => $ee_content[$fields_map['local_tax_incent_program_link_3']],
		    'local_tax_incent_program_info_3' => $ee_content[$fields_map['local_tax_incent_program_info_3']],
		    'additional_link_1' => $ee_content[$fields_map['additional_link_1']],
		    'additional_info_1' => $ee_content[$fields_map['additional_info_1']],
		    'additional_link_2' => $ee_content[$fields_map['additional_link_2']],
		    'additional_info_2' => $ee_content[$fields_map['additional_info_2']],
		    'additional_link_3' => $ee_content[$fields_map['additional_link_3']],
		    'additional_info_3' => $ee_content[$fields_map['additional_info_3']],
		    'additional_link_4' => $ee_content[$fields_map['additional_link_4']],
		    'additional_info_4' => $ee_content[$fields_map['additional_info_4']],
		    'additional_link_5' => $ee_content[$fields_map['additional_link_5']],
		    'additional_info_5' => $ee_content[$fields_map['additional_info_5']],
		))
		  ->execute();
		  
	return TRUE;	  
}

function loadPublicRecordsState($entryId, $vid, $ee_content, $fields_map) {
	global $pub_rec_state;	
	$nid = db_insert('public_records_state')
		  ->fields(array(
		    'vid' => $vid,
		    'public_records_site_link' => $ee_content[$fields_map['public_records_site_link']],
		    'state_home_page_link' => $ee_content[$fields_map['state_home_page_link']],
		    'dept_assess_tax_link' => $ee_content[$fields_map['dept_assess_tax_link']],
		    'bus_econ_dev_link' => $ee_content[$fields_map['bus_econ_dev_link']],
		    'elections_info_link' => $ee_content[$fields_map['elections_info_link']],
		    'data_center_link' => $ee_content[$fields_map['data_center_link']],
		    'state_known_info' => $ee_content[$fields_map['state_known_info']],
		    'electronic_res_link_1' => $ee_content[$fields_map['electronic_res_link_1']],
		    'state_realtor_assoc_link' => $ee_content[$fields_map['state_realtor_assoc_link']],
		    'state_licensing_info_link' => $ee_content[$fields_map['state_licensing_info_link']],
		    'state_tax_incent_program_link_1' => $ee_content[$fields_map['state_tax_incent_program_link_1']],
		    'state_tax_incent_program_info_1' => $ee_content[$fields_map['state_tax_incent_program_info_1']],
		    'state_tax_incent_program_link_2' => $ee_content[$fields_map['state_tax_incent_program_link_2']],
		    'state_tax_incent_program_info_2' => $ee_content[$fields_map['state_tax_incent_program_info_2']],
		    'additional_link_1' => $ee_content[$fields_map['additional_link_1']],
		    'additional_info_1' => $ee_content[$fields_map['additional_info_1']],
		    'additional_link_2' => $ee_content[$fields_map['additional_link_2']],
		    'additional_info_2' => $ee_content[$fields_map['additional_info_2']],
		    'additional_link_3' => $ee_content[$fields_map['additional_link_3']],
		    'additional_info_3' => $ee_content[$fields_map['additional_info_3']],
		  ))
		  ->execute();
	$assoc = sprintf('%s', $entryId);
	$pub_rec_state[$assoc] = $vid;	  
	return TRUE;	  
}

function loadPublicRecordsFederal($entryId, $vid, $ee_content, $fields_map) {
	global $pub_rec_fed;
	$nid = db_insert('public_records_federal')	
		->fields(array(
		    'vid' => $vid,
		    'dept_hud_link' => $ee_content[$fields_map['dept_hud_link']],
		    'national_register_historic_link' => $ee_content[$fields_map['national_register_historic_link']],
		    'heritage_pres_services_link' => $ee_content[$fields_map['heritage_pres_services_link']],
		    'national_trust_historic_pres_link' => $ee_content[$fields_map['national_trust_historic_pres_link']],
		    'fema_link' => $ee_content[$fields_map['fema_link']],
		    'nars_link' => $ee_content[$fields_map['nars_link']],
		    'additional_link_1' => $ee_content[$fields_map['additional_link_1']],
		    'additional_info_1' => $ee_content[$fields_map['additional_info_1']],
		    'additional_link_2' => $ee_content[$fields_map['additional_link_2']],
		    'additional_info_2' => $ee_content[$fields_map['additional_info_2']],
		    'additional_link_3' => $ee_content[$fields_map['additional_link_3']],
		    'additional_info_3' => $ee_content[$fields_map['additional_info_3']],
		  ))
		  ->execute();
	$pub_rec_fed['entryId'] = $entryId;	  
	$pub_rec_fed['vId'] = $vid;	  
	return TRUE;
}

function getCategoryName($ee, $entryId) {
	$sql = 'SELECT a.cat_id, a.cat_name, b.entry_id FROM '.$ee['prefix'].'categories a, '.$ee['prefix'].'category_posts b WHERE (b.cat_id = a.cat_id) and b.entry_id='.$entryId;
	$cat = mysqlQuery($ee,$sql, TRUE);
	return $cat['cat_name'];
}

function comment_save_modified($edit) {
// Import the comment in the database.
db_query("INSERT INTO {comments} (nid, comment, timestamp, name, mail, homepage) VALUES (%d, '%s', %d, '%s', '%s', '%s')", $edit->nid, $edit->comment,$edit->timestamp, $edit->name, $edit->mail, $edit->homepage);

_comment_update_node_statistics($edit->nid);

// Tell the other modules a new comment has been submitted.
comment_invoke_comment($edit, 'insert');

// Clear the cache so an anonymous user can see his comment being added.
cache_clear_all();
}

function taxonomy_node_save_modified($nid, $terms) {

  if ($terms) {
    foreach ($terms as $term) {
      if ($term) {
        db_query("INSERT INTO {term_node} (nid, tid) VALUES (%d, %d)", $nid, $term);
      }
    }
  }
}

function ImportComments($ee,$nid,$eeid) {
    $sql = "SELECT `name`,`email`,`url`,`comment_date`,`comment` FROM `".$ee['prefix']."comments` WHERE `entry_id`=$eeid";
    $Comments = mysqlQuery($ee,$sql);
    foreach ($Comments as $Comments) {
     $comment->nid = $nid;
     $comment->comment = $Comments['comment'];
     $comment->timestamp= $Comments['comment_date'];
     $comment->homepage= $Comments['url'];
     $comment->mail = $Comments['email'];
     $comment->name = $Comments['name'];
comment_save_modified($comment);
     $numComments = $numComments + 1;
    }
return ($numComments);
}

function MatchCategories($ee,$nid,$eeid) {
$sql = "SELECT cat_id FROM ".$ee['prefix']."category_posts WHERE entry_id=$eeid";
$Category_numbers = mysqlQuery($ee,$sql);

foreach ($Category_numbers as $Category_number) {
  $sql = "SELECT cat_name FROM ".$ee['prefix']."categories WHERE cat_id=$Category_number[cat_id]";
  $terms = mysqlQuery($ee,$sql);
  foreach ($terms as $term) {
   $matches = taxonomy_get_term_by_name($term['cat_name']);
   foreach ($matches as $match) {
    taxonomy_node_save_modified($nid, array($match->tid));
    echo "Entry <i>$nid</i> is categorized as <i>$term[cat_name]</i><br />";
   }
  }
}
}

?>