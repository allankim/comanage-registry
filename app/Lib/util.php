<?php
/**
 * COmanage Registry Utilities
 *
 * Copyright (C) 2010-15 University Corporation for Advanced Internet Development, Inc.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright     Copyright (C) 2010-15 University Corporation for Advanced Internet Development, Inc.
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry
 * @since         COmanage Registry v0.1
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @version       $Id$
 */

// Group hierarchy separator. XXX This should probably be moved elsewhere and made a constant
global $group_sep;
$group_sep = ":";

// Default invitation validity, in minutes (used in various places, should probably be moved elsewhere)
define("DEF_INV_VALIDITY", 1440);

/**
 * Find an attribute within an array, specifically intended for working with
 * Enrollment Flow Attributes.
 *
 * @since  COmanage Registry v0.1
 * @param  array An indexed array (ie: [0], [1], [2], etc) of CMP Enrollment Flow Attributes
 * @param  string The attribute to search for (ie: $attrs[#]['attribute'])
 * @param  string The type to search for (ie: $attrs[#]['type'])
 * @return array An array equivalent to $attrs[#] matching $attr and $type, with an additional field of '_index' corresponding to the position (#) the match was found at; or false if not found
 */

function find_ef_attribute($attrs, $attr, $type=null)
{
  foreach(array_keys($attrs) as $k)
  {
    if($attrs[$k]['attribute'] == $attr)
    {
      if(!defined($type)
         || (defined($attrs[$k]['type'])
             && $attrs[$k]['type'] == $type))
      {
        $ret = $attrs[$k];
        $ret['_index'] = $k;
        return($ret);
      }
    }
  }
  
  return(false);
}

/**
 * Render a telephone number in E.123 format
 *
 * @since  COmanage Registry v0.9.4
 * @param  Array $phone Array of TelephoneNumber attributes
 * @return string The formatted telephone number
 */

function formatTelephone($phone) {
  $n = "";
  
  if(!empty($phone['country_code'])) {
    // We'll only output + style if a country code was provided
    $n = "+" . $phone['country_code'];
  }
  
  if(!empty($phone['area_code'])) {
    if($n != "") {
      $n .= " ";
    }
    
    $n .= $phone['area_code'];
  }
  
  if(!empty($phone['number'])) {
    if($n != "") {
      $n .= " ";
    }
    
    $n .= $phone['number'];
  }
  
  if(!empty($phone['extension'])) {
    if($n != "") {
      $n .= " " . _txt('fd.telephone.ext');
    }
    
    $n .= $phone['extension'];
  }
  
  return $n;
}

/**
 * Assemble a common name from the array $name.
 *
 * @since  COmanage Registry v0.1
 * @param  array An array containing the attributes of a Name object
 * @param  boolean If true return honorific as part of name
 * @return string The assembled name
 */

function generateCn($name, $showHonorific = false) {
  // Name order is a bit tricky. We'll use the language encoding as our hint, although
  // it isn't perfect. This could be replaced with a more sophisticated test as
  // requirements evolve.
  
  $cn = "";
  
  if(empty($name['language'])
     || !in_array($name['language'], array('hu', 'ja', 'ko', 'za-Hans', 'za-Hant'))) {
    // Western order. Do not show honorific by default.
    
    if($showHonorific && !empty($name['honorific'])) {
      $cn .= ($cn != "" ? ' ' : '') . $name['honorific'];
    }
    
    if(!empty($name['given'])) {
      $cn .= ($cn != "" ? ' ' : '') . $name['given'];
    }
    
    if(!empty($name['middle'])) {
      $cn .= ($cn != "" ? ' ' : '') . $name['middle'];
    }
    
    if(!empty($name['family'])) {
      $cn .= ($cn != "" ? ' ' : '') . $name['family'];
    }
    
    if(!empty($name['suffix'])) {
      $cn .= ($cn != "" ? ' ' : '') . $name['suffix'];
    }
  } else {
    // Switch to Eastern order. It's not clear what to do with some components.
    
    if(!empty($name['family'])) {
      $cn .= ($cn != "" ? ' ' : '') . $name['family'];
    }
    
    if(!empty($name['given'])) {
      $cn .= ($cn != "" ? ' ' : '') . $name['given'];
    }
  }
  
  return $cn;
}

/**
 * Obtain the preferred language requested by the browser, if supported.
 *
 * @since  COmanage Registry v0.8.2
 * @return string Language code, or an empty string
 */

function getPreferredLanguage() {
  $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
  
  if($lang == 'zh') {
    // For the Chinese scripts, determine traditional vs simplified.
    // First map old style notation to new style.
    
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
    
    if($lang == 'zh-CN') {
      return 'zh-Hans';
    }
    if($lang == 'zh-TW') {
      return 'zh-Hant';
    }
    
    // Still here? Maybe it's new style.
    
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 7);
    
    if($lang == 'zh-Hans' || $lang == 'zh-Hant') {
      return $lang;
    }
    
    // Else we don't know what to do with this Chinese variant. Go with simplified.
    
    return 'zh-Hans';
  }
  
  // See if this is a defined language.
  
  global $cm_lang, $cm_texts;

  if(isset($cm_texts[ $cm_lang ]['en.lang'][ $lang ])) {
    return $lang;
  }
  
  // We don't recognize this language
  
  return "";
}

/**
 * Process a message template, replacing parameters with respective values.
 * Note this function is for configured templates (ie: those loaded from the
 * database) and not for Cake templates (ie: those loaded from View/Emails).
 *
 * The substitutions array should include key/value pairs for each value
 * to replace. For example array('CO_NAME' => 'MyCO') will replace @CO_NAME
 * with "MyCO".
 *
 * @since  COmanage Registry v0.9
 * @param  String Template text
 * @param  Array Array of substitution parameters
 * @return String Processed template
 */

function processTemplate($template, $subtitutions) {
  $searchKeys = array();
  $replaceVals = array();
  
  foreach(array_keys($subtitutions) as $k) {
    $searchKeys[] = "(@" . $k . ")";
    $replaceVals[] = $subtitutions[$k];
  }
  
  return str_replace($searchKeys, $replaceVals, $template);  
}

/**
 * Render menu links for plugin-defined menu items.
 * - postcondition: HTML emitted
 *
 * @param HtmlHelper Helper to use to render links
 * @param Array Array of plugins as created by AppController
 * @param String Which menu items to render
 * @param Integer Co Id
 */

function render_plugin_menus() {
  // When called from dropMenu element there may be 4 arguments and
  // when called from secondaryMenu element there may be 3 arguments.
  $htmlHelper = func_get_arg(0);
  $plugins = func_get_arg(1);
  $menu = func_get_arg(2);
  $coid = null;
  
  if(func_num_args() == 4
     && ($menu == 'coconfig' || $menu == 'copeople')){
    $coid = func_get_arg(3);
  }
  
  if(!empty($plugins)) {
    foreach(array_keys($plugins) as $plugin) {
      if(isset($plugins[$plugin][$menu])) {
        foreach(array_keys($plugins[$plugin][$menu]) as $label) {
          $args = $plugins[$plugin][$menu][$label];
          $args['plugin'] = Inflector::underscore($plugin);
          if(!empty($coid)){
            $args['co'] = $coid;
          }
          print "<li>" . $htmlHelper->link($label, $args) . "</li>\n";
        }
      }
    }
  }
}

/**
 * Escape a string so it is suitable for echoing into Javascript function parameters.
 * Specifically, quotes are double escaped for correct round-trip rendering.
 *
 * @since  COmanage Registry v0.1
 * @param  string String to be escaped
 * @return string Escaped string
 */

function _jtxt($str) {
  return(str_replace(array("'", '"'), array('\\x27', '\\x22'), $str));
}
