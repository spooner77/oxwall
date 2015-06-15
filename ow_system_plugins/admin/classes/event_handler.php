<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CLASS_EventHandler
{
    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind('admin.disable_fields_on_edit_profile_question', array($this, 'onGetDisableActionList'));
        $eventManager->bind('admin.disable_fields_on_edit_profile_question', array($this, 'onGetJoinStampDisableActionList'), 999);
        $eventManager->bind('admin.add_admin_notification', array($this, 'addAdminNotification'));
        $eventManager->bind('admin.add_admin_warning', array($this, 'addAdminWarning'));
    }

    public function addAdminWarning( ADMIN_CLASS_NotificationCollector $coll )
    {
        if ( OW::getConfig()->configExists('base', 'cron_is_active') && (int) OW::getConfig()->getValue('base', 'cron_is_active') === 0 )
        {
            $coll->addWarning(
                OW::getLanguage()->text('admin', 'warning_cron_is_not_active', 
                        array('path' => OW_DIR_ROOT . 'ow_cron' . DS . 'run.php')),

                'cron_active'
            );
        }

        if ( !ini_get('allow_url_fopen') )
        {
            $coll->addWarning(
                OW::getLanguage()->text('admin', 'warning_url_fopen_disabled'),
                'fopen_disabled'
            );
        }
    }

    public function addAdminNotification( ADMIN_CLASS_NotificationCollector $coll )
    {
        // update soft
        if ( OW::getConfig()->getValue('base', 'update_soft') )
        {
            $coll->addNotice(
                OW::getLanguage()->text('admin', 'notification_soft_update', 
                        array('link' => OW::getRouter()->urlForRoute('admin_core_update_request'))),

                'soft_update'
            );
        }

        $pluginsCount = BOL_PluginService::getInstance()->getPluginsToUpdateCount();

        // plugins update
        if ( $pluginsCount > 0 )
        {
            $coll->addNotice(
                OW::getLanguage()->text('admin', 'notification_plugins_to_update', 
                        array('link' => OW::getRouter()->urlForRoute('admin_plugins_installed'), 'count' => $pluginsCount)),

                'plugin_update'
            );
        }

        $themesCount = BOL_ThemeService::getInstance()->getThemesToUpdateCount();

        // themes update
        if ( $themesCount > 0 )
        {
            $coll->addNotice(
                OW::getLanguage()->text('admin', 'notification_themes_to_update', 
                        array('link' => OW::getRouter()->urlForRoute('admin_themes_choose'), 'count' => $themesCount)),

                'theme_update'
            );
        }
    }

    public function onGetDisableActionList( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name != 'joinStamp' )
        {
            $dto = $params['questionDto'];

            foreach ( $data as $key => $value )
            {
                switch($key)
                {
                    case 'disable_account_type' :
                        
                        if ( $dto->base == 1 )
                        {
                            $data['disable_account_type'] = true;
                        }
                        
                        break;
                    case 'disable_answer_type' :

                        if ( $dto->base == 1 )
                        {
                            $data['disable_answer_type'] = true;
                        }
                        
                        break;
                    case 'disable_presentation' :

                        if ( $dto->base == 1 )
                        {
                            $data['disable_presentation'] = true;
                        }
                        
                        break;
                    case 'disable_column_count' :
                                                
                        if ( !empty($dto->parent) )
                        {
                            $data['disable_column_count'] = true;
                        }
                        
                        break;
                        
                    case 'disable_possible_values' :
                        
                        if ( !empty($dto->parent) )
                        {
                            $data['disable_possible_values'] = true;
                        }
                        
                        break;
                    
                    case 'disable_display_config' :

                        if ( $dto->name == 'joinStamp' )
                        {
                            $data['disable_display_config'] = true;
                        }

                        break;
                    case 'disable_required' :
                        
                        if ( $dto->base == 1 )
                        {
                            $data['disable_required'] = true;
                        }

                        
                        break;
                    case 'disable_on_join' :

                        if ( in_array($dto->name, array('password') ) || $dto->base == 1 )
                        {
                            $data['disable_on_join'] = true;
                        }

                        break;
                    case 'disable_on_view' :
                        if ( in_array($dto->name, array('password') ) )
                        {
                            $data['disable_on_view'] = true;
                        }
                        break;
                    case 'disable_on_search' :
                        if ( in_array($dto->name, array('password') ) )
                        {
                            $data['disable_on_search'] = true;
                        }
                        break;
                    case 'disable_on_edit' :
                        if ( in_array($dto->name, array('password') ) )
                        {
                            $data['disable_on_edit'] = true;
                        }
                        break;
                }
            }
        }

        $e->setData($data);
    }
    
    function onGetJoinStampDisableActionList( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name == 'joinStamp' )
        {
            $disableActionList = array(
                'disable_account_type' => true,
                'disable_answer_type' => true,
                'disable_presentation' => true,
                'disable_column_count' => true,
                'disable_display_config' => true,
                'disable_possible_values' => true,
                'disable_required' => true,
                'disable_on_join' => true,
                'disable_on_view' => false,
                'disable_on_search' => true,
                'disable_on_edit' => true
            );

            $e->setData($disableActionList);
        }
    }
}
