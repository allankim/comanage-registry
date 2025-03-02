<!--
/**
 * COmanage Registry File Source Plugin Fields
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v2.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
-->
<?php
  // Determine if fields are editable
  $e = false;
  
  if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
    $e = true;
    
  // We shouldn't get here if we don't have at least read permission, but check just in case
  
  if(!$e && !$permissions['view'])
    return false;
  
  // Add breadcrumbs
  print $this->element("coCrumb");

  $args = array();
  $args['plugin'] = null;
  $args['controller'] = 'org_identity_sources';
  $args['action'] = 'index';
  $args['co'] = $cur_co['Co']['id'];
  $this->Html->addCrumb(_txt('ct.org_identity_sources.pl'), $args);

  $args = array();
  $args['plugin'] = null;
  $args['controller'] = 'org_identity_sources';
  $args['action'] = 'edit';
  $args[] = $vv_oisid;
  $this->Html->addCrumb($file_sources[0]['OrgIdentitySource']['description'], $args);

  $crumbTxt = _txt('op.config');
  $this->Html->addCrumb($crumbTxt);
  
  print $this->Form->hidden('org_identity_source_id', array('default' => $vv_oisid)) . "\n";
?>
<div class="co-info-topbox">
  <i class="material-icons">info</i>
  <?php print _txt('pl.filesource.info'); ?>
</div>
<ul id="<?php print $this->action; ?>_file_source_target" class="fields form-list form-list-admin">
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.filesource.filepath'); ?>
        <span class="required">*</span>
      </div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('filepath', array('size' => 50)) : filter_var($file_sources[0]['FileSource']['filepath'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print ($e ? $this->Form->label('format', _txt('pl.filesource.format')) : _txt('pl.filesource.format')); ?>
        <span class="required">*</span>
      </div>
    </div>
    <div class="field-info">
      <?php
        global $cm_lang, $cm_texts;
        
        $attrs = array();
        $attrs['value'] = (isset($file_sources[0]['FileSource']['format']) ? $file_sources[0]['FileSource']['format'] : "");
        $attrs['empty'] = true;

        if($e) {
          print $this->Form->select('format',
                                    $cm_texts[ $cm_lang ]['pl.filesource.en.format'],
                                    $attrs);

          if($this->Form->isFieldError('format')) {
            print $this->Form->error('format');
          }
        } else {
          print _txt('pl.filesource.en.format', null, filter_var($file_sources[0]['FileSource']['format'],FILTER_SANITIZE_SPECIAL_CHARS));
        }
      ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.filesource.archivedir'); ?>
      </div>
      <div class="field-desc"><?php print _txt('pl.filesource.archivedir.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('archivedir', array('size' => 50)) : filter_var($file_sources[0]['FileSource']['archivedir'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.filesource.threshold_warn'); ?>
      </div>
      <div class="field-desc"><?php print _txt('pl.filesource.threshold_warn.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('threshold_warn') : filter_var($file_sources[0]['FileSource']['threshold_warn'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.filesource.threshold_override'); ?>
      </div>
      <div class="field-desc"><?php print _txt('pl.filesource.threshold_override.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print $this->Form->input('threshold_override'); ?>
    </div>
  </li>
  <?php if($e): ?>
    <li class="fields-submit">
      <div class="field-name">
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </div>
      <div class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
      </div>
    </li>
  <?php endif; ?>
</ul>
