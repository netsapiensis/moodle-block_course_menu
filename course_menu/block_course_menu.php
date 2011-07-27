<?php

class block_course_menu extends block_base
{
    function init() {
        $this->title = get_string('blockname', 'block_course_menu');
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->version = 2010100101;
    }

	function instance_allow_config() {
	    return true;
	}

    function instance_config_print() {
        global $CFG;

        // required stuff
        $sections = $this->getSections();
        $icons = $this->getLinkIcons();
        $expandableTreeText = $this->getName("expandable_tree");
    	$this->course = get_record('course', 'id', $this->instance->pageid);
        
	    // if any config is missing then set eveything to default
    	if ((!is_array($this->config))
    	    || (empty($this->config['elements']))
    		|| (empty($this->config['expandableTree']))
        	|| (empty($this->config['chapters']) || ((isset($chapCount) && isset($sectCount)) && ($chapCount > $sectCount)))
        	|| (!isset($this->config['links'])))
        {
            // no instance configs
        	if ($this->oldConfigsV1()) {

                // import configs from Course Menu + version 2
        	    $this->config = $this->importConfigsFromV1();

            } elseif ($this->oldConfigsV2()) {

                //import configs from Course Menu + version 1
                $this->config['chapters'] = $this->importConfigsFromV2();

        	} elseif (!empty($CFG->block_course_menu)) { // try global config
                // use global configs
        		$this->config = unserialize($CFG->block_course_menu);

	        	// add chaptering
		    	$this->config['chapEnable'] = 0;
                $this->config['subChapEnable'] = 0;
                $this->config['subChaptersCount'] = 1;
			    $this->config['chapters']   = array();

			    $chapter = array();
			    $chapter['name']  = get_string("chapter", "block_course_menu")." 1";
			    $chapter['childElements'] = array();
                $chapter['childElements'][0] = array();
                $chapter['childElements'][0]['type'] = "subchapter";
                $chapter['childElements'][0]['name'] = get_string("subchapter", "block_course_menu") . " 1-1";
                $chapter['childElements'][0]['count'] = count($sections);
			    $this->config['chapters'][] = $chapter;

			    // refresh the name of the element having id = 'tree'
        		foreach ($this->config['elements'] as $k => $element) {
			    	if ($element['id'] == 'tree') {
			    		$this->config['elements'][$k]['name'] = $this->getName('tree');
			    	}
			    }
                if (count($this->config['elements']) == 0) {
					$this->config = $this->defaultConfig();
				}
		    } else {
		    	// no instance nor global configs nor old version to import from - use default
        		$this->config = $this->defaultConfig();
        	}

        } else {
        	// redo chaptering if the number of the sctions changed
    	    $sumChapSections = 0;
    	    $b = true;

    	    foreach ($this->config['chapters'] as $k => $chapter) {
    	    	foreach ($chapter['childElements'] as $kk => $child) {
    	    		if ($child['type'] == 'subchapter') {
        				$sumChapSections += $child['count'];
    	    		} else {
    	    			$sumChapSections ++;
    	    		}
    	    	}
        	}
        	$sectCount = count($sections);
        	$chapCount = count($this->config['chapters']);
    	    if ($sumChapSections != $sectCount) {

                if ($chapCount <= $sectCount) {

                    $c = floor($sectCount / $chapCount);

                    if (($sectCount - ($c*($chapCount - 1)) > $c) && ($sectCount - (($c+1)*($chapCount - 1)) > 0)) {
                        $c++;
                    }

                    for ($i = 0; $i < $chapCount; $i++) {
                        $temp = $i < $chapCount - 1 ? $c : $sectCount - ($c*($chapCount - 1));
                        $j = 0;
                        while ($this->config['chapters'][$i]['childElements'][$j]['type'] == "topic") {
                            $j++;
                        }
                        $newChild = $this->config['chapters'][$i]['childElements'][$j];
                        $newChild['count'] = $temp;
                        $this->config['chapters'][$i]['childElements'] = array();
                        $this->config['chapters'][$i]['childElements'][0] = $newChild;
                    }

                } else {
                    for ($i = 0; $i < $sectCount; $i++) {
                        $j = 0;
                        while ($this->config['chapters'][$i]['childElements'][$j]['type'] == "topic") {
                            $j++;
                        }
                        $newChild = $this->config['chapters'][$i]['childElements'][$j];
                        $newChild['count'] = 1;
                        $this->config['chapters'][$i]['childElements'] = array();
                        $this->config['chapters'][$i]['childElements'][0] = $newChild;
                    }
                    for ($i = $sectCount; $i < $chapCount; $i++) {
                        unset($this->config['chapters'][$i]);
                    }
                    $chapCount = $sectCount;
                }
                $this->config['subChaptersCount'] = count($this->config['chapters']);

                // the number of sections has changed so the chaptering has changed so write the new changes to the database
                $this->save_config_to_db();
            }
        }

        // elements: set names
        foreach ($this->config['elements'] as $k => $element) {
        	$this->config['elements'][$k]['name'] = $this->getName($element['id']);
        }
        // make the output
        if (is_file($CFG->dirroot .'/blocks/'. $this->name() .'/config_instance.php')) {
            print_box_start();
            include($CFG->dirroot .'/blocks/'. $this->name() .'/config_instance.php');
            print_box_end();
        } else {
            notice(get_string('blockconfigbad'), str_replace('blockaction=', 'dummy=', qualified_me()));
        }

        return true;
    }

    function instance_config_save($data) {
        $_SESSION['truncated'][$this->course->id] = ''; // clear the saved truncate lengths

    	$newData = $this->buildConfigFromRequest($data);
    	$this->config = $newData;
	    return $this->save_config_to_db();
    }

    function save_config_to_db() {
    	return set_field('block_instance', 'configdata', base64_encode(serialize($this->config)), 'id', $this->instance->id);
    }

    function has_config() {
    	return true;
    }

    function config_print() {
        global $CFG, $THEME;
        // required stuff
        $icons = $this->getLinkIcons();
        $expandableTreeText = $this->getName("expandable_tree");

	    // if any config is missing then set eveything to default
    	if (empty($CFG->block_course_menu)) {
        	$this->config = $this->defaultConfig();
        } else {
        	$this->config = unserialize($CFG->block_course_menu);
        }

        // elements: set names
        foreach ($this->config['elements'] as $k => $element) {
        	$this->config['elements'][$k]['name'] = $this->getName($element['id']);
        }

		if (count($this->config['elements']) == 0) {
			$this->config = $this->defaultConfig();
		}
        $defaultConfig = $this->defaultConfig();

        print_box_start();
        include($CFG->dirroot.'/blocks/'.$this->name().'/config_global.php');
        print_box_end();
        return true;
    }

    function config_save($config) {
        $_SESSION['truncated'] = ''; // clear the saved truncate lengths

    	$newData = $this->buildConfigFromRequest($config);
    	$this->config = $newData;

    	set_config('block_course_menu', serialize($newData));
        return true;
    }

    function defaultConfig() {
    	global $CFG, $USER;

    	// elements -------------------------------------------------------------------------
    	$elements   = array();

   		$elements[] = $this->createElement("tree", $this->getName("tree"), '', '', 0);

	   		// showallsections
   		$elements[] = $this->createElement(
   			"showallsections",
   			$this->getName("showallsections"),
   			"",
   			"{$CFG->wwwroot}/blocks/course_menu/icons/viewall.gif"
   		);

	   		// controlpanel
   		if (!isguest()) {
	        $elements[] = $this->createElement(
	         	"controlpanel",
	           	$this->getName("controlpanel"),
	           	"",
	           	"{$CFG->wwwroot}/blocks/course_menu/icons/configure.gif"
	         );
        }

	   		// blogmenu
        $elements[] = $this->createElement(
   			"blogmenu",
   			$this->getName("blogmenu"),
   			"",
   			"{$CFG->wwwroot}/blocks/course_menu/icons/blog.gif",
   			1, 0
   		);

	   		// messages
   		$elements[] = $this->createElement(
   			"messages",
   			$this->getName("messages"),
   			"/message/index.php",
   			"{$CFG->wwwroot}/blocks/course_menu/icons/messages.gif",
   			1, 0
   		);

	   		// calendar
   		$elements[] = $this->createElement(
   			"calendar",
   			$this->getName("calendar"),
   			"",
   			"{$CFG->wwwroot}/blocks/course_menu/icons/cal.gif",
   			1, 0
   		);

	   		// showgrades
   		if ((isset($this->course->showgrades)) && ($this->course->showgrades)) {
            $elements[] = $this->createElement(
            	"showgrades",
            	$this->getName("showgrades"),
            	"",
            	"{$CFG->pixpath}/i/grades.gif",
   			1, 0
            );
		}

	   		// troubleticket
		if (file_exists($CFG->dirroot.'/blocks/trouble_ticket/block_trouble_ticket.php')) {
            $elements[] = $this->createElement(
            	"troubleticket",
            	$this->getName("troubleticket"),
            	"",
            	"{$CFG->wwwroot}/blocks/trouble_ticket/icons/bug.gif",
   			1, 0
            );
    	}

    	$config = array();
    	$config['elements'] = $elements;

    	// expandable tree ------------------------------------------------------------------
   		$config['expandableTree']['enable'] = 0;
    	$config['expandableTree']['text']   = $this->getName("expandable_tree");

    	// sections -------------------------------------------------------------------------
    	$sections = $this->getSections();

    	// chaptering -----------------------------------------------------------------------
       	$config['chapEnable'] = 0;
        $config['subChapEnable'] = 0;
        $config['subChaptersCount'] = 1;
        $config['chapters']   = array();

        $chapter = array();
        $chapter['name']  = get_string("chapter", "block_course_menu")." 1";

        $child = array();
        $child['type'] = "subchapter";
		$child['name'] = get_string("subchapter", "block_course_menu") . " 1";
        $child['count'] = count($sections);
        $chapter['childElements'] = array();
        $chapter['childElements'][0] = $child;

        $config['chapters'][] = $chapter;

        // links ----------------------------------------------------------------------------
        $config['linksEnable'] = 0;
        $config['links']       = array();

        return $config;
    }

    function buildConfigFromRequest($data) {
        $data    = stripslashes_recursive($data);
	    $newData = array();

        // elements
	    $newData['elements'] = array();
    	foreach ($data->ids as $k => $id) {
    		$url     = $data->urls[$k];
    		$icon    = $data->icons[$k];
    		$canHide = $data->canHides[$k];
    		$visible = $data->visibles[$k];
    		$name    = $this->getName($id);
    		$newData['elements'][] = $this->createElement($id, $name, $url, $icon, $canHide, $visible);
    	}

    	// expandableTree
    	$newData['expandableTree'] = array();
    	$newData['expandableTree']['enable'] = $data->expandableTree;
    	//$newData['expandableTree']['text']   = $data->expandableTree;

        if ($data->chapEnable == 0) {
            $data->subChapEnable = 0;
        }

    	//subchapters
    	$newData['subChapEnable'] 		= $data->subChapEnable;
    	$newData['subChaptersCount']	= $data->subChaptersCount;
    	// chapters
    	$newData['chapEnable'] = $data->chapEnable;

    	$newData['chapters'] = array();


    	$lastIndex = 0;

    	$total = 0;
    	foreach ($data->chapterNames as $k => $name) {
    		$chapter = array();
    		$chapter['name']  = $name;
    		$chapter['childElements'] = array();

            for ($i = $lastIndex; $i < $lastIndex + $data->chapterChildElementsNumber[$k]; $i++) {
                $child = array();
                if ($data->chapEnable == 0) { //only one subchapter
                    $child['type'] = "subchapter";
                    $child['count'] = count($this->getSections());
                    $child['name'] = get_string("subchapter", "block_course_menu") . " 1-1";
                } elseif ($data->subChapEnable == 0) {
                    $child['type'] = "subchapter";
                    $xx = $k + 1;
                    $child['name'] = get_string("subchapter", "block_course_menu") . " {$xx}-1";
                    $child['count'] = $data->chapterCounts[$k];
                } else {
                    $child['type'] = $data->childElementTypes[$i];
                    if ($child['type'] == "subchapter") {
                        $child['count'] = $data->childElementCounts[$i];
                        $total += $child['count'];
                        $child['name'] = $data->childElementNames[$i];
                    }
                }
                $chapter['childElements'][] = $child;
            }
            $lastIndex = $i;
    		$newData['chapters'][] = $chapter;
    	}

    	// links
    	$newData['linksEnable'] = $data->linksEnable;
    	$newData['links'] = array();
    	if (isset($data->linkNames)) { // means: if instance config. we don't have links in global config
	    	foreach ($data->linkNames as $k => $name) {
	    		$link = array();
	    		$link['name']   = $name;
	    		$link['target'] = $data->linkTargets[$k];
	    		$link['icon']   = $data->linkIcons[$k];

	    		// url
	    		$link['url'] = $data->linkUrls[$k];
	    		if (strpos($data->linkUrls[$k], "://") === false) {
	    			// if no protocol then add "http://" - [CM-TD2]
	    			$link['url'] = "http://".$link['url'];
	    		}

	    		// checkbox configs
	    		$idx = "keeppagenavigation$k";
	    		$link['keeppagenavigation'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		$idx = "allowresize$k";
	    		$link['allowresize'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		$idx = "allowresize$k";
	    		$link['allowresize'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		$idx = "allowresize$k";
	    		$link['allowresize'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		$idx = "allowscroll$k";
	    		$link['allowscroll'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		$idx = "showdirectorylinks$k";
	    		$link['showdirectorylinks'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		$idx = "showlocationbar$k";
	    		$link['showlocationbar'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		$idx = "showmenubar$k";
	    		$link['showmenubar'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		$idx = "showtoolbar$k";
	    		$link['showtoolbar'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		$idx = "showstatusbar$k";
	    		$link['showstatusbar'] = (isset($data->$idx)) && ($data->$idx == "on") ? 1 : 0;

	    		// defaultwidth + defaultheight
	    		$link['defaultwidth'] = $data->defaultwidth[$k];
	    		$link['defaultheight'] = $data->defaultheight[$k];

	    		$newData['links'][] = $link;
	    	}
    	}

    	return $newData;
    }

    function get_content() {
        global $CFG, $USER;
        $this->course = get_record('course', 'id', $this->instance->pageid);
        $sections = $this->getSections();
        
        // courseFormat
        $courseFormat = $this->course->format == 'topics' ? 'topic' : 'week';

        // expandableTreeText
        $expandableTreeText = $this->getName("expandable_tree");

        // if any config is missing then set eveything to default
    	if ((empty($this->config)) || (!is_array($this->config))) {
        	// no instance configs
        	if ($this->oldConfigsV1()) {
        	    // import configs from the Course Menu + version 2
        	    $this->config = $this->importConfigsFromV1();
            } elseif ($this->oldConfigsV2()) { //destul de sigur de asta nu o sa mai fie nevoie
                //import configs from v1 of Course Menu

                $this->config['chapters'] = $this->importConfigsFromV2();

        	} elseif (!empty($CFG->block_course_menu)) { // try global config
        		// use global configs
        		$this->config = unserialize($CFG->block_course_menu);

	        	// add chaptering
		    	$this->config['chapEnable'] = 0;
			    $this->config['chapters']   = array();

                $this->config['subChapEnable'] = 0;
                $this->config['subChaptersCount'] = 1;

			    $chapter = array();
			    $chapter['name']  = get_string("chapter", "block_course_menu")." 1";

                $chapter['childElements'] = array();
			    $child = array();
                $child['name'] = get_string("subchapter", "block_course_menu") . " 1";
                $child['type'] = "subchapter";
                $child['count'] = count($sections);
                $chapter['childElements'][] = $child;
			    $this->config['chapters'][] = $chapter;
				if (count($this->config['elements']) == 0) {
					$this->config = $this->defaultConfig();
				}


        	} else {
        		// no instance nor global configs nor old version to import from - use default
        		$this->config = $this->defaultConfig();
        	}
        }

        foreach ($this->config['chapters'] as $k => $chapter) {
            if (isset($chapter['count']) && $chapter['count']) {
                $this->config['chapters'][$k]['childElements'] = array();
			    $child = array();
                $xx = $k + 1;
                $child['name'] = get_string("subchapter", "block_course_menu") . " " . $xx . "1";
                $child['type'] = "subchapter";
                $child['count'] = $chapter['count'];
                $this->config['chapters'][$k]['childElements'][] = $child;
                unset ($this->config['chapters'][$k]['count']);
            }
        }


        // elements
        $elements = $this->config['elements'];
    	foreach ($elements as $k => $element) {
    		// set iconClasses
    		if (!empty($elements[$k]['icon'])) {
	    		$iconClass = $elements[$k]['icon'];
	    		$iconClass = substr($iconClass, strrpos($iconClass, '/') + 1);
	    		$iconClass = substr($iconClass, 0, strrpos($iconClass, '.'));
	    		$iconClass = preg_replace('/[^a-zA-Z0-9]+/', '-', $iconClass);
	    		$iconClass = "icon".$iconClass;
    			$elements[$k]['iconClass'] = $iconClass;
    		} else {
    			$elements[$k]['iconClass'] = "";
    		}
    		// set names
    		$elements[$k]['name'] = $this->getName($elements[$k]['id']);

		    // refresh course specific urls - bugs [CM-B6] and [CM-B15]
	    	switch ($elements[$k]['id']) {
    		case 'showallsections':
    			$courseFormat = $this->course->format == 'topics' ? 'topic' : 'week';
    			$elements[$k]['url'] = "$CFG->wwwroot/course/view.php?id={$this->course->id}&$courseFormat=all";
    			break;

    		case 'controlpanel':
    			$elements[$k]['url'] = $CFG->wwwroot."/blocks/course_menu/controls/controls.php?action=mcp&id=".$this->course->id;
    			break;

    		case 'calendar':
    			$elements[$k]['url'] = "$CFG->wwwroot/calendar/view.php?view=upcoming&course=" .$this->course->id;
    			break;

    		case 'showgrades':
    			$elements[$k]['url'] = $CFG->wwwroot."/grade/index.php?id=".$this->course->id;
    			break;

    		case 'troubleticket':
    			$elements[$k]['url'] = "{$CFG->wwwroot}/blocks/trouble_ticket/form.php?id={$this->course->id}";
    			break;

    		case 'blogmenu':
    			$elements[$k]['url'] = $CFG->wwwroot.'/blog/index.php?userid='.$USER->id.'&courseid=1';
    			break;
	    	}
    	}

        $isSectionRequest = false;
        $highlight = true;


        
        $allWeek = optional_param($courseFormat, "", PARAM_CLEAN);

        if ($allWeek == "all") {
            $highlight = false;
        }
        
        // displaysection - current section
		$week = optional_param($courseFormat, -1, PARAM_INT);
        
        if ($week != -1) {
            if ($week == 0 && !empty($_SESSION["display_section_{$this->course->id}"])) {
                $displaysection = $_SESSION["display_section_{$this->course->id}"];
            } else {
                $displaysection = course_set_display($this->course->id, $week);
                $isSectionRequest = true;
            }
            
		} else {
            if (isset($USER->display[$this->course->id])) {
                $displaysection = $USER->display[$this->course->id];
		    } else {
		        $displaysection = course_set_display($this->course->id, 0);
		    }
		}
        $_SESSION["display_section_{$this->course->id}"] = $displaysection;


    	// section names
        foreach ($sections as $k => $section) {
    		foreach ($section['resources'] as $l => $resource) {
	    		$iconClass = $resource['icon'];
	    		$iconClass = substr($iconClass, strrpos($iconClass, '/') + 1);
	    		$iconClass = substr($iconClass, 0, strrpos($iconClass, '.'));
	    		$iconClass = preg_replace('/[^a-zA-Z0-9]+/', '-', $iconClass);
	    		$iconClass = "icon".$iconClass;
	    		$sections[$k]['resources'][$l]['iconClass'] = $iconClass;
    		}
    	}

		// links
		$links = $this->config['links'];
    	foreach ($links as $k => $link) {
    		if (!empty($link['icon'])) {
	    		$iconClass = $link['icon'];

	    		$lastDir = substr($iconClass, 0, strrpos($iconClass, '/'));
	    		$lastDir = substr($lastDir, strrpos($lastDir, '/') + 1);

	    		$iconClass = substr($iconClass, strrpos($iconClass, '/') + 1);
	    		$iconClass = substr($iconClass, 0, strrpos($iconClass, '.'));
	    		$iconClass = preg_replace('/[^a-zA-Z0-9]+/', '-', $iconClass);
	    		$iconClass = "icon".$lastDir.$iconClass;
    			$links[$k]['iconClass'] = $iconClass;
    		} else {
    			$links[$k]['iconClass'] = "";
    		}
    	}

    	// redo chaptering if the number of the sctions changed
	    $sumChapSections = 0;
        $subChapterCount = 0;
    	foreach ($this->config['chapters'] as $k => $chapter) {
            foreach ($chapter['childElements'] as $kk => $child) {
                if ($child['type'] == "subchapter") {
                    $subChapterCount ++;
                    $sumChapSections += $child['count'];
                } else {
                    $sumChapSections ++;
                }
            }
    	}

        $sectCount = count($sections);
    	$chapCount = count($this->config['chapters']);


	    if ($sumChapSections != $sectCount) {

	        if ($chapCount <= $sectCount) {

                $c = floor($sectCount / $chapCount);

                if (($sectCount - ($c*($chapCount - 1)) > $c) && ($sectCount - (($c+1)*($chapCount - 1)) > 0)) {
                    $c++;
                }

                for ($i = 0; $i < $chapCount; $i++) {
                    $temp = $i < $chapCount - 1 ? $c : $sectCount - ($c*($chapCount - 1));
                    $j = 0;
                    while ($this->config['chapters'][$i]['childElements'][$j]['type'] == "topic") {
                        $j++;
                    }
                    $newChild = $this->config['chapters'][$i]['childElements'][$j];
                    $newChild['count'] = $temp;
                    $this->config['chapters'][$i]['childElements'] = array();
                    $this->config['chapters'][$i]['childElements'][0] = $newChild;
                }


	        } else {
                    // make 1 section / chapter; eliminate ($chapCount - $sectCount) chapters, from the last ones
                for ($i = 0; $i < $sectCount; $i++) {
                    $j = 0;
                    while ($this->config['chapters'][$i]['childElements'][$j]['type'] == "topic") {
                        $j++;
                    }
                    $newChild = $this->config['chapters'][$i]['childElements'][$j];
                    $newChild['count'] = 1;
                    $this->config['chapters'][$i]['childElements'] = array();
                    $this->config['chapters'][$i]['childElements'][0] = $newChild;
                }
                for ($i = $sectCount; $i < $chapCount; $i++) {
                    unset($this->config['chapters'][$i]);
                }
                $chapCount = $sectCount;
	        }
            $this->config['subChaptersCount'] = count($this->config['chapters']);

	        // the number of sections has changed so the chaptering has changed so write the new changes to the database
	        $this->save_config_to_db();
	    }

	    $this->content->footer = "";
    	// make the output
        ob_start();
		include($CFG->dirroot .'/blocks/'. $this->name() .'/content.php');
		$this->content->text = ob_get_contents();
        ob_end_clean();
        return $this->content;
    }

    function get_scriptless_content() {
    }

	// truncates the description to fit within the given $max_size. Splitting on tags and \n's where possible
	// @param $string: string to truncate
	// @param $max_size: length of largest piece when done
	// @param $trunc: string to append to truncated pieces
	function truncate_description($string, $max_size=20, $trunc = '...') {
	    $split_tags = array('<br>','<BR>','<Br>','<bR>','</dt>','</dT>','</Dt>','</DT>','</p>','</P>', '<BR />', '<br />', '<bR />', '<Br />');
	    $temp = $string;

	    foreach($split_tags as $tag) {
	    	list($temp) = explode($tag, $temp, 2);
	    }
	    $rstring = strip_tags($temp);

	    $rstring = html_entity_decode($rstring);

	    if (strlen($rstring) > $max_size) {
	        $rstring = chunk_split($rstring, ($max_size-strlen($trunc)), "\n");
	        $temp = explode("\n", $rstring);
	        // catches new lines at the beginning
	        if (trim($temp[0]) != '') {
	            $rstring = trim($temp[0]).$trunc;
	        }
	        else {
	           $rstring = trim($temp[1]).$trunc;
	        }
	    }
	    if (strlen($rstring) > $max_size) {
	        $rstring = substr($rstring, 0, ($max_size - strlen($trunc))).$trunc;
	    }
	    elseif($rstring == '') {
	        // we chopped everything off... lets fall back to a failsafe but harsher truncation
	        $rstring = substr(trim(strip_tags($string)),0,($max_size - strlen($trunc))).$trunc;
	    }

	    // single quotes need escaping
	    return str_replace("'", "\\'", $rstring);
	}

	function clearEnters($string) {
	    $newstring = str_replace(chr(13),' ',str_replace(chr(10),' ',$string));
	    return $newstring;
	}

	function createElement($id, $name, $url, $icon = "", $canHide = 1, $visible = 1) {
	    $elem = array();
	    $elem['id']      = $id;
	    $elem['name']    = $name;
	    $elem['url']     = $url;
	    $elem['icon']    = $icon;
	    $elem['canHide'] = $canHide;
	    $elem['visible'] = $visible;

		return $elem;
	}

	function getName($elementId) {
	    if (isset($this->instance->pageid)) {
		    $course = get_record('course', 'id', $this->instance->pageid);
		    $format = $course->format;
	    } else {
	        $format = '';
	    }

		switch ($elementId) {
			case 'calendar':     return get_string('calendar','calendar');
			case 'showgrades':   return get_string('gradebook', 'grades');
			case 'sectiongroup': return get_string("name".$format);
			case 'tree':
			    if ($format == 'topics') {
			        return get_string('topics', 'block_course_menu');
			    } elseif ($format == 'weeks') {
			        return get_string('weeks', 'block_course_menu');
			    } else {
			    	return get_string('topicsweeks', 'block_course_menu');
			    }
			default: return get_string($elementId, "block_course_menu");
		}
	}

    function getSections() {
    	global $CFG, $USER;

    	if (isset($this->instance->pageid)) {
        	$this->course = get_record('course', 'id', $this->instance->pageid);
        	get_all_mods($this->course->id, $mods, $modnames, $modnamesplural, $modnamesused);
        	$isteacher = isteacher($this->course->id);
        	$courseFormat = $this->course->format == 'topics' ? 'topic' : 'week';

            // displaysection - current section
    		$week = optional_param('week', -1, PARAM_INT);
    		if ($week != -1) {
    		    $displaysection = course_set_display($this->course->id, $week);
    		} else {
    		    if (isset($USER->display[$this->course->id])) {
    		        $displaysection = $USER->display[$this->course->id];
    		    } else {
    		        $displaysection = course_set_display($this->course->id, 0);
    		    }
    		}

        	$genericName  = get_string("name".$this->course->format);
    		$allSections  = get_all_sections($this->course->id);

    		$sections = array();
    		foreach ($allSections as $k => $section) {
    			if ($k <= $this->course->numsections) { // get_all_sections() may return sections that are in the db but not displayed because the number of the sections for this course was lowered - bug [CM-B10]
    				if (!empty($section)) {
    		        	$newSec = array();
    		        	$newSec['visible'] = $section->visible;

    		        	// name
    		            if (!empty($section->summary)) {
    		            	$strsummary = trim($section->summary);
    		            } else {
    		                $strsummary = ucwords($genericName)." ".$k; // just a default name
    		            }

    		            $strsummary = $this->truncate_description($strsummary, 200);
    		            $strsummary = trim($this->clearEnters($strsummary));
    		            $newSec['name'] = $strsummary;

    		            // url
    		            if ($displaysection != 0) {
    		            	$newSec['url'] = "{$CFG->wwwroot}/course/view.php?id={$this->course->id}&$courseFormat=$k";
    		            } else {
    		            	$newSec['url'] = "#section-$k";
    		            }

    		            // resources
    		            $modinfo = unserialize($this->course->modinfo);

    		            $newSec['resources'] = array();
    		            $sectionmods = explode(",", $section->sequence);
    			        foreach ($sectionmods as $modnumber) {
    			            if (empty($mods[$modnumber])) {
    			                continue;
    			            }
    			            $mod = $mods[$modnumber];
    			            if ($mod->visible or $isteacher) {
    			                $instancename = urldecode($modinfo[$modnumber]->name);
    			                if (!empty($CFG->filterall)) {
    			                    $instancename = filter_text($instancename, $this->course->id);
    			                }

    			                if (!empty($modinfo[$modnumber]->extra)) {
    			                    $extra = urldecode($modinfo[$modnumber]->extra);
    			                }
    			                else {
    			                    $extra = "";
    			                }

    			                // don't do anything for labels
    			                if ($mod->modname != 'label') {
    			                    // Normal activity
    			                    if ($mod->visible) {
    			                        if (!strlen(trim($instancename))) {
    			                            $instancename = $mod->modfullname;
    			                        }
    			                        $instancename = $this->truncate_description($instancename);

    			                        $resource = array();
    			                        if ($mod->modname != 'resource') {
    			                        	$resource['name'] = $this->truncate_description($instancename, 200);
    			                        	$resource['url']  = "$CFG->wwwroot/mod/$mod->modname/view.php?id=$mod->id";
    			                        	$resource['icon'] = "$CFG->modpixpath/$mod->modname/icon.gif";
    			                        } else {
    			                            require_once($CFG->dirroot.'/mod/resource/lib.php');
    			                            $info = resource_get_coursemodule_info($mod);
    			                            if (isset($info->icon)) {
    				                        	$resource['name'] = $this->truncate_description($info->name, 200);
    				                        	$resource['url']  = "$CFG->wwwroot/mod/$mod->modname/view.php?id=$mod->id";
    			                            	$resource['icon'] = "$CFG->pixpath/{$info->icon}";
    			                            } else if(!isset($info->icon)) {
    				                        	$resource['name'] = $this->truncate_description($info->name, 200);
    				                        	$resource['url']  = "$CFG->wwwroot/mod/$mod->modname/view.php?id=$mod->id";
    			                            	$resource['icon'] = "$CFG->modpixpath/$mod->modname/icon.gif";
    			                            }
    			                        }
    			                        $newSec['resources'][] = $resource;
    			                    }
    			                }

    			            }
    			        }
    					//hide hidden sections from students if the course settings say that - bug #212
    		            $coursecontext = get_context_instance(CONTEXT_COURSE, $this->course->id);
    	    			if (!($section->visible == 0 && !has_capability('moodle/course:viewhiddensections', $coursecontext))) {
							$sections[] = $newSec;
    	    			}
    		        }
    			}
    	    }

    	    // get rid of the first one
    	    array_shift($sections);

    	    return $sections;
    	}
    	return array();
    }

	function getLinkIcons() {
    	global $CFG;

    	$icons = array();
		$icons[0]['name'] = get_string('noicon', 'block_course_menu');
		$icons[0]['img']  = '';
		$icons[1]['name'] = get_string('linkfileorsite', 'block_course_menu');
		$icons[1]['img']  = "{$CFG->wwwroot}/blocks/course_menu/icons/link.gif";
		$icons[2]['name'] = get_string('displaydirectory', 'block_course_menu');
		$icons[2]['img']  = "{$CFG->wwwroot}/blocks/course_menu/icons/directory.gif";

    	$allmods = get_records("modules");
		foreach ($allmods as $mod) {
			$icon = array();
			$icon['name'] = get_string("modulename", $mod->name);
			$icon['img']  = $CFG->modpixpath.'/'.$mod->name.'/icon.gif';

			$icons[] = $icon;
		}

    	return $icons;
	}

	/**
	 * check if there are some configs from an version 2 of course menu +
	 */
	function oldConfigsV2() {
        return  isset($this->config['chapters'][0]['count']);
	}

    /**
     * check if there are some configs from version 1 of course menu +
     */
    function oldConfigsV1() {
        return isset($this->config->id0);
    }

	/**
	 * imports configs from an version 2 of course menu +
	 *
	 */
	function importConfigsFromV2() {
	    global $CFG, $USER;

    	if (is_array($this->config)) {

        	// sections -------------------------------------------------------------------------
        	$sections = $this->getSections();

        	// chaptering -----------------------------------------------------------------------
           	$config['chapEnable'] = $this->config['chapEnable'];
            $this->config['subChapEnable'] = 0;

           	if ($config['chapEnable']) {
                $config['chapters'] = $this->config['chapters'];
                for ($i = 0; $i < count($config['chapters']); $i++) {
                    $child = array();
                    $child['type'] = "subchapter";
                    $child['name'] = get_string("subchapter", "block_course_menu") . " 1-1";
                    $child['count'] = $config['chapters'][$i]['count'];
                    $config['chapters'][$i]['childElements'] = array();
                    $config['chapters'][$i]['childElements'][0] = $child;
                    unset($config['chapters'][$i]['count']);
                }

           	} else {
                $config['chapters']   = array();
                $chapter = array();
                $chapter['name']  = get_string("chapter", "block_course_menu")." 1";
                $child = array();
                $child['type'] = "subchapter";
                $child['name'] = get_string("subchapter", "block_course_menu") . " 1-1";
                $child['count'] = count($sections);
                $chapter['childElements'] = array();
                $chapter['childElements'][0] = $child;
                $config['chapters'][] = $chapter;
           	}
            $this->config['subChaptersCount'] = count($config['chapters']);
            
            return $config['chapters'];
    	}
    	return null; // this should never happen
	}

    function importConfigsFromV1() {

        global $CFG, $USER;

    	if (is_object($this->config)) {
        	// elements -------------------------------------------------------------------------
        	$elements   = array();
        	for ($i = 0; $i <= 8; $i++) {
        	    $id = 'id'.$i;
        	    $id = $this->config->$id;

        	    switch ($id) {
        	    case 'tree':
           		    $elements[] = $this->createElement("tree", $this->getName("tree"), '', '', 0);
           		    break;

        	    case 'showallsections':
               		$elements[] = $this->createElement(
               			"showallsections",
               			$this->getName("showallsections"),
               			"",
               			"{$CFG->wwwroot}/blocks/course_menu/icons/viewall.gif",
               			1, ($this->config->showallsections == 'show' ? 1 : 0)
               		);
               		break;

        	    case 'controlpanel':
               		if (!isguest()) {
            	        $elements[] = $this->createElement(
            	         	"controlpanel",
            	           	$this->getName("controlpanel"),
            	           	"",
            	           	"{$CFG->wwwroot}/blocks/course_menu/icons/configure.gif",
            	           	1, ($this->config->controlpanel == 'show' ? 1 : 0)
            	         );
                    }
                    break;

        	    case 'blogmenu':
        	        $elements[] = $this->createElement(
               			"blogmenu",
               			$this->getName("blogmenu"),
               			"",
               			"{$CFG->wwwroot}/blocks/course_menu/icons/blog.gif",
               			1, ($this->config->blogmenu == 'show' ? 1 : 0)
               		);
               		break;

        	   	case 'messages':
        	        $elements[] = $this->createElement(
               			"messages",
               			$this->getName("messages"),
               			"/message/index.php",
               			"{$CFG->wwwroot}/blocks/course_menu/icons/messages.gif",
               			1, ($this->config->messages == 'show' ? 1 : 0)
               		);
               		break;

        	   	case 'calendar':
        	        $elements[] = $this->createElement(
               			"calendar",
               			$this->getName("calendar"),
               			"",
               			"{$CFG->wwwroot}/blocks/course_menu/icons/cal.gif",
               			1, ($this->config->calendar == 'show' ? 1 : 0)
               		);
               		break;

        	   	case 'showgrades':
        	        if ((isset($this->course->showgrades)) && ($this->course->showgrades)) {
                        $elements[] = $this->createElement(
                        	"showgrades",
                        	$this->getName("showgrades"),
                        	"",
                        	"{$CFG->pixpath}/i/grades.gif",
               			    1, ($this->config->gradebook == 'show' ? 1 : 0)
                        );
            		}
            		break;

        	   	case 'troubleticket':
        	        if (file_exists($CFG->dirroot.'/blocks/trouble_ticket/block_trouble_ticket.php')) {
                        $elements[] = $this->createElement(
                        	"troubleticket",
                        	$this->getName("troubleticket"),
                        	"",
                        	"{$CFG->wwwroot}/blocks/trouble_ticket/icons/bug.gif",
               			    1, ($this->config->troubleticket == 'show' ? 1 : 0)
                        );
                	}
                	break;
        		}
        	}

        	$config = array();
        	$config['elements'] = $elements;

        	// expandable tree ------------------------------------------------------------------
        	$config['expandableTree']['enable'] = $this->config->expandable_tree == "show" ? 1 : 0;
        	$config['expandableTree']['text']   = $this->getName("expandable_tree");

        	// sections -------------------------------------------------------------------------
        	$sections = $this->getSections();

        	// chaptering -----------------------------------------------------------------------
           	$config['chapEnable'] = $this->config->chaptering == "show" ? 1 : 0;
            $config['subChapEnable'] = 0;
           	if ($config['chapEnable']) {
                $config['chapters']   = array();

                $chapName  = explode('|&|', $this->config->chapName);
                $chapCount = explode('|&|', $this->config->chapCount);

                $chapter = array();
                for ($i = 0; $i < $this->config->chapters; $i++) {

                    $chapter['name']  = $chapName[$i];
                    $chapter['childElements'] = array();

                    $child = array();
                    $number = $i + 1;
                    $child['name'] = get_string("subchapter", "block_course_menu") . "{$nr}-1";
                    $child['type'] = "subchapter";
                    $child['count'] = $chapCount[$i];
                    $chapter['childElements'][0] = $child;
                    $config['chapters'][] = $chapter;
                }

           	} else {
                $config['chapters']   = array();
                $chapter = array();
                $chapter['name']  = get_string("chapter", "block_course_menu")." 1";
                $child = array();
                $child['type'] = "subchapter";
                $child['name'] = get_string("subchapter", "block_course_menu") . " 1-1";
                $child['count'] = count($sections);
                $chapter['childElements'] = array();
                $chapter['childElements'][0] = $child;
                $config['chapters'][] = $chapter;
           	}
            $config['subChaptersCount'] = count($config['chapters']);

            // links ----------------------------------------------------------------------------
            $config['linksEnable'] = 0;
            $config['links']       = array();

            return $config;
    	}
    	return null; // this should never happen

    }

}

?>